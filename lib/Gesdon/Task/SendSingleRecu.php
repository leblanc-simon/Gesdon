<?php

namespace Gesdon\Task;

use Gesdon\Core\Config;
use Gesdon\Core\Exception;
use Gesdon\Database\DonPeer;
use Gesdon\Database\Donateur;
use Gesdon\Database\DonateurPeer;
use Gesdon\Database\DonateurQuery;
use Gesdon\Database\RecuFiscal;
use Gesdon\Database\RecuFiscalPeer;
use Gesdon\Database\RecuFiscalQuery;
use Gesdon\Utils\RecuPdf;


class SendSingleRecu extends BaseTask
{
  /**
   * Connexion PDO
   * @var     PDO
   * @access  private
   */
  private $con = null;
  
  private $debut       = null;
  private $fin         = null;
  private $donateur_id = null;
  private $email       = null;
  
  /**
   * Constructeur
   *
   * @param   array   $args   Les arguments de la tâche
   * @access  public
   * @see     BaseTask::__construct
   */
  public function __construct($args = array())
  {
    parent::__construct($args);
    
    if (isset($args[0]) === false || isset($args[1]) === false || isset($args[2]) === false) {
      throw new Exception('La tâche attend 2 arguments : date de début et date de fin');
    }
    
    if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $args[0]) === 0 || preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $args[1]) === 0) {
      throw new Exception('Date de début et date de fin doivent être au format anglais : yyyy-mm-dd');
    }

    if (is_numeric($args[2]) === false) {
      throw new Exception('L\'identifiant du donateur doit etre un numerique');
    }
    
    $this->debut = new \DateTime($args[0]);
    $this->fin = new \DateTime($args[1]);
    $this->donateur_id = (int)$args[2];
    $this->fin->setTime(23, 59, 59);
    
    // Si l'utilisateur a inversé début et fin, on corrige automatiquement (oui, l'utilisateur est con par défaut :-))
    if ($this->debut > $this->fin) {
      list($this->debut, $this->fin) = array($this->fin, $this->debut);
    }
  }
  
  
  /**
   * Lancement de la tâche
   *
   * @access  public
   */
  public function run()
  {
    if ($this->generateRecuDb() === true) {
      $this->generateRecuPdf();
      $this->sendPdf();
    }
  }
  
  
  /**
   * Génére en base de données les données pour les reçus
   *
   * @return  bool    Vrai si aucune erreur, faux sinon
   * @access  private
   */
  private function generateRecuDb()
  {
    $this->logSection(__METHOD__, 'begin');
    
    $no_error = true;
    
    $sql = 'SELECT * FROM donateur WHERE ident_paiement IN'
           .'(SELECT DISTINCT(don.ident_paiement) FROM don'
           .' WHERE date_paiement >= :debut'
           .' AND date_paiement <= :fin'
           .' AND statut_paiement = :status)'
           .' AND id = :id';
    
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->bindValue(':debut', $this->debut->format('Y-m-d'), \PDO::PARAM_STR);
    $stmt->bindValue(':fin', $this->fin->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
    $stmt->bindValue(':status', DonPeer::STATUT_OK, \PDO::PARAM_STR);
    $stmt->bindValue(':id', $this->donateur_id, \PDO::PARAM_INT);
    
    if ($stmt->execute() === false) {
      $this->logSection(__METHOD__, 'Erreur la récupération des donateurs', BaseTask::ERROR);
      throw new Exception('impossible de finir la tâche');
    }
    
    $formater = new \PropelObjectFormatter();
    $formater->setClass('Gesdon\Database\Donateur');
    $donateurs = $formater->format($stmt);
    
    foreach ($donateurs as $donateur) {
      try {
        $recu_fiscal = \Gesdon\Utils\Convert::donnateurToRecuFical($donateur, $this->debut, $this->fin);
        $this->email = $donateur->getEmail();
        $this->logSection(__METHOD__, 'La conversion du donateur : '.$donateur->getId().' a été réalisée : '.$recu_fiscal->getId());
      } catch (\Exception $e) {
        $no_error = false;
        $this->logSection(__METHOD__, 'Erreur la conversion du donateur : '.$donateur->getId(), BaseTask::ERROR);
        continue;
      }
    }
    
    $this->logSection(__METHOD__, 'end');
    
    return $no_error;
  }
  
  
  /**
   * Génére les reçus au format PDF
   *
   * @access  private
   */
  private function generateRecuPdf()
  {
    $this->logSection(__METHOD__, 'begin');
    
    $recus = RecuFiscalQuery::create()
                ->filterByDateDonDebut(array('min' => $this->debut, 'max' => $this->fin))
                ->filterByEnvoye(false)
                ->filterByEmail($this->email)
                ->find();
    
    foreach ($recus as $recu) {
      $this->logSection(__METHOD__, 'Génération du PDF pour le reçu : '.$recu->getId());
      $recu_pdf = new RecuPdf();
      $recu_pdf->init($recu);
      $recu_pdf->generatePDF(true);
    }
    
    $this->logSection(__METHOD__, 'end');
  }
  
  
  /**
   * Envoie l'ensemble des reçus non envoyé
   */
  private function sendPdf()
  {
    $this->logSection(__METHOD__, 'begin');
    
    $recus = RecuFiscalQuery::create()
                ->filterByDateDonDebut(array('min' => $this->debut, 'max' => $this->fin))
                ->filterByEnvoye(false)
                ->filterByPays('France')
                ->filterByEmail($this->email)
                ->find();
    
    foreach ($recus as $recu) {
      try {
        $email = $recu->getEmail();
        if (empty($email) === true) {
          $this->logSection(__METHOD__, 'L\'envoi du reçu n\'est pas possible pour un donateur sans adresse email');
          continue;
        }
        
        // On récupère le texte à envoyer
        $message_type = $this->initMessage($recu, $this->getMessage($recu));
        
        // On initialise le mail
        $message = \Swift_Message::newInstance();
        $message->setSubject($this->getSubject($recu));
        $message->setBody($message_type, 'text/plain');
        $message->setFrom(Config::get('mail_from'));
        $message->setReplyTo(Config::get('mail_from'));
        $mail_bcc = Config::get('mail_bcc');
        if (empty($mail_bcc) === false) {
          $message->setBcc($mail_bcc);
        }
        $message->setTo(array($email => $recu->getNom().' '.$recu->getPrenom()));
        
        // On inclue le PDF du reçu
        $filename = Config::get('pdf_dir').DIRECTORY_SEPARATOR.$recu->getFilename();
        if (file_exists($filename) === true) {
          $attachment = \Swift_Attachment::fromPath($filename, 'application/pdf');
          $attachment->setFilename('recu_framasoft_'.$recu->getNumero().'.pdf');
          $message->attach($attachment);
        } else {
          $this->logSection(__METHOD__, 'Le fichier '.$filename.' n\'existe pas', BaseTask::ERROR);
        }
        
        // On prépare le transport
        if (Config::get('smtp') === true) {
          $transport = \Swift_SmtpTransport::newInstance(Config::get('smtp_server'), Config::get('smtp_port'), Config::get('smtp_secure'));
          if (Config::get('smtp_user') !== null) {
            $transport->setUsername(Config::get('smtp_user'));
            $transport->setPassword(Config::get('smtp_pass'));
          }
        } else {
          $transport = \Swift_MailTransport::newInstance();
        }
        
        $mailer = \Swift_Mailer::newInstance($transport);
        
        // On envoi le mail
        if (!$mailer->send($message, $failures)) {
          throw new \Exception('Failure send : '.implode(', ', $failures));
        } else {
          $this->logSection(__METHOD__, 'L\'envoi du reçu '.$recu->getId().' a été effectué');
        }
        
        // On marque le reçu comme envoyé
        $recu->setEnvoye(true);
        $recu->save();
        
        // On vide la mémoire 
        unset($mailer, $transport, $message, $failures);
        
      } catch (\Exception $e) {
        $this->logSection(__METHOD__, $e->getMessage(), BaseTask::ERROR);
      }
    }
    
    $this->logSection(__METHOD__, 'end');
  }
  
  
  /**
   * Concerti un message type en message pour le mail
   *
   * @param   RecuFiscal  $recu     Le reçu fiscal
   * @param   string      $message  Le message type
   * @return  string                Le message avec les variables remplacées
   * @access  private
   */
  private function initMessage(RecuFiscal $recu, $message)
  {
    $vars = array(
      'id'            => $recu->getNumero(),
      'date_don'      => $recu->getDateDonTexte(),
      'moyen_paiement'=> $recu->getMoyenPaiement(),
      'montant'       => $recu->getMontant(),
      'nom'           => $recu->getNom(),
      'prenom'        => $recu->getPrenom(),
      'adresse'       => $recu->getRue(),
      'cp'            => $recu->getCp(),
      'ville'         => $recu->getVille(),
      'pays'          => $recu->getPays(),
      'email'         => $recu->getEmail()
    );
    
    foreach ($vars as $key => $value) {
      $message = str_replace('%%'.$key.'%%', $value, $message);
    }
    
    return $message;
  }
  
  
  /**
   * Récupère le message en fonction du reçu fiscal
   *
   * @param   RecuFiscal  $recu     Le reçu fiscal
   * @return  string      $message  Le message type
   * @access  private
   */
  private function getMessage(RecuFiscal $recu)
  {
    if ($recu->getRecurrent() === true) {
      return Config::get('mail_message_recurrent');
    } else {
      return Config::get('mail_message');
    }
  }
  
  
  /**
   * Récupère le sujet en fonction du reçu fiscal
   *
   * @param   RecuFiscal  $recu     Le reçu fiscal
   * @return  string      $message  Le message type
   * @access  private
   */
  private function getSubject(RecuFiscal $recu)
  {
    if ($recu->getRecurrent() === true) {
      return Config::get('mail_subject_recurrent');
    } else {
      return Config::get('mail_subject');
    }
  }
  
  
  /**
   * Récupère une connexion à la base de données
   *
   * @return  PDO   une connexion à la base de données
   * @access  private
   */
  private function getConnection()
  {
    if ($this->con === null) {
      $this->con = \Propel::getConnection(DonateurPeer::DATABASE_NAME, \Propel::CONNECTION_WRITE);
    }
    
    return $this->con;
  }
}
