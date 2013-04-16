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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SendRecu extends BaseTask
{
  private $debut  = null;
  private $fin    = null;
  
  /**
   * Configuration de la tâche
   */
  protected function configure()
  {
    $this
          ->setName('send:recus')
          ->setDescription('Envoyer les reçus fiscaux pour les dons effectués entre 2 dates')
          ->addArgument('debut',
                        InputArgument::REQUIRED,
                        'Date de début pour la recherche des dons'
          )
          ->addArgument('fin',
                        InputArgument::REQUIRED,
                        'Date de fin pour la recherche des dons'
          )
          ->addOption('build-only', null, InputOption::VALUE_NONE, 'Génére les reçus mais ne les envoi pas', null);
  }
  
  
  /**
   * Execution de la tâche
   *
   * @param   \Symfony\Component\Console\Input\InputInterface   $input  les entrées de la console
   * @param   \Symfony\Component\Console\Output\OutputInterface $output les sortie de la console
   * @access  protected
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->setOutput($output);
    
    $debut      = $input->getArgument('debut');
    $fin        = $input->getArgument('fin');
    $build_only = $input->getOption('build-only');
    
    if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $debut) === 0 || preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $fin) === 0) {
      throw new Exception('Date de début et date de fin doivent être au format anglais : yyyy-mm-dd');
    }
    
    $this->debut = new \DateTime($debut);
    $this->fin = new \DateTime($fin);
    
    // Si l'utilisateur a inversé début et fin, on corrige automatiquement (oui, l'utilisateur est con par défaut :-))
    if ($this->debut > $this->fin) {
      list($this->debut, $this->fin) = array($this->fin, $this->debut);
    }
    
    // On place la fin à 23:59:59 pour récupérer tous les dons (sans oublier la dernière journée)
    $this->fin->setTime(23, 59, 59);
    
    // On lance la tâche
    $this->log('Lancement de la tâche');
    
    if ($this->generateRecuDb() === true) {
      $this->generateRecuPdf();
      if ($build_only === false) {
        $this->sendPdf();
      } else {
        $this->log('Option "build-only" activé : pas d\'envoi de mail');
      }
    } else {
      $this->log('Erreur lors de la génération de la base de données des reçus', 'error');
    }
    
    $this->log('Fin du traitement de la tâche');
  }
  
  
  /**
   * Génére en base de données les données pour les reçus
   *
   * @return  bool    Vrai si aucune erreur, faux sinon
   * @access  private
   */
  private function generateRecuDb()
  {
    $this->log('begin');
    
    $no_error = true;
    
    $sql = 'SELECT * FROM donateur WHERE ident_paiement IN'
           .'(SELECT DISTINCT(don.ident_paiement) FROM don'
           .' WHERE date_paiement >= :debut'
           .' AND date_paiement <= :fin'
           .' AND statut_paiement = :status)';
    
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->bindValue(':debut', $this->debut->format('Y-m-d'), \PDO::PARAM_STR);
    $stmt->bindValue(':fin', $this->fin->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
    $stmt->bindValue(':status', DonPeer::STATUT_OK, \PDO::PARAM_STR);
    
    if ($stmt->execute() === false) {
      $this->log('Erreur la récupération des donateurs', 'error');
      throw new Exception('impossible de finir la tâche');
    }
    
    $formater = new \PropelObjectFormatter();
    $formater->setClass('Gesdon\Database\Donateur');
    $donateurs = $formater->format($stmt);
    
    foreach ($donateurs as $donateur) {
      try {
        $recu_fiscal = \Gesdon\Utils\Convert::donnateurToRecuFical($donateur, $this->debut, $this->fin);
        $this->log('La conversion du donateur : '.$donateur->getId().' a été réalisée : '.$recu_fiscal->getId());
      } catch (\Exception $e) {
        $no_error = false;
        $this->log('Erreur la conversion du donateur : '.$donateur->getId(), 'error');
        continue;
      }
    }
    
    $this->log('end');
    
    return $no_error;
  }
  
  
  /**
   * Génére les reçus au format PDF
   *
   * @access  private
   */
  private function generateRecuPdf()
  {
    $this->log('begin');
    
    $recus = RecuFiscalQuery::create()
                ->filterByDateDonDebut(array('min' => $this->debut, 'max' => $this->fin))
                ->filterByEnvoye(false)
                ->find();
    
    foreach ($recus as $recu) {
      $this->log('Génération du PDF pour le reçu : '.$recu->getId());
      $recu_pdf = new RecuPdf();
      $recu_pdf->init($recu);
      $recu_pdf->generatePDF(true);
    }
    
    $this->log('end');
  }
  
  
  /**
   * Envoie l'ensemble des reçus non envoyé
   */
  private function sendPdf()
  {
    $this->log('begin');
    
    $recus = RecuFiscalQuery::create()
                ->filterByDateDonDebut(array('min' => $this->debut, 'max' => $this->fin))
                ->filterByEnvoye(false)
                ->filterByPays('France')
                ->find();
    
    foreach ($recus as $recu) {
      if (Config::get('mail_attente') !== null) {
        $this->log('On attend '.Config::get('mail_attente').' secondes');
        sleep(Config::get('mail_attente'));
      }
      
      try {
        $email = $recu->getEmail();
        if (empty($email) === true) {
          $this->log('L\'envoi du reçu n\'est pas possible pour un donateur sans adresse email', 'comment');
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
          $this->log('Le fichier '.$filename.' n\'existe pas', 'error');
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
          $this->log('L\'envoi du reçu '.$recu->getId().' a été effectué');
        }
        
        // On marque le reçu comme envoyé
        $recu->setEnvoye(true);
        $recu->save();
        
        // On vide la mémoire 
        unset($mailer, $transport, $message, $failures);
        
      } catch (\Exception $e) {
        $this->log($e->getMessage(), 'error');
      }
    }
    
    $this->log('end');
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
}
