<?php

namespace Gesdon\Task;

use Gesdon\Core\Exception;
use Gesdon\Database\Don;
use Gesdon\Database\DonPeer;
use Gesdon\Database\DonQuery;
use Gesdon\Database\Donateur;
use Gesdon\Database\DonateurPeer;
use Gesdon\Database\DonateurQuery;
use Gesdon\Database\CmcicInfo;
use Gesdon\Database\CmcicInfoPeer;
use Gesdon\Database\CmcicInfoQuery;
use Gesdon\Database\PaypalInfo;
use Gesdon\Database\PaypalInfoPeer;
use Gesdon\Database\PaypalInfoQuery;

class Migrate extends BaseTask
{
  /**
   * Connexion PDO
   * @var     PDO
   * @access  private
   */
  private $con = null;
  
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
  }
  
  /**
   * Lancement de la tâche
   *
   * @access  public
   */
  public function run()
  {
    $this->processCmcic();
    $this->processCmcicRecurrent();
    $this->processPaypal();
    $this->processCheques();
  }
  
  
  /**
   * Traitement des données de paypal
   *
   * @access  private
   */
  private function processCmcic()
  {
    $sql = 'SELECT * FROM zz_cmcic WHERE code_retour = \'paiement\'';
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->execute();
    
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
      $this->logSection(__METHOD__, 'Traitement de la ligne '.$row['id']);
      
      try {
        $this->getConnection()->beginTransaction();
        // Création du donateur
        $donateur = DonateurQuery::create()->filterByIdentPaiement($row['reference'])->findOneOrCreate();
        
        $fields = array(
          'nom' => 'setNom',
          'prenom' => 'setPrenom',
          'mail' => 'setEmail',
          'adresse1' => 'setRue',
          'cp' => 'setCp',
          'ville' => 'setVille',
          'pays' => 'setPays',
          'reference' => 'setIdentPaiement',
          'date' => 'setDateCreation',
          'texte_libre' => 'setCommentaire',
        );
        
        foreach ($fields as $old => $method) {
          if (empty($row[$old]) === false) {
            $donateur->$method(trim($row[$old]));
          }
        }
        
        if (empty($row['adresse2']) === false) {
          $donateur->setRue($donateur->getRue()."\n".trim($row['adresse2']));
        }
        
        $donateur->setTypeDonateur(DonateurPeer::PARTICULIER);
        $donateur->save();
        
        // Création du don (uniquement pour les dons simple : TPE == 0829374)
        if ($row['tpe'] == '0829374') {
          $don = new Don();
          $don->setIdentPaiement(trim($row['reference']));
          $don->setMontant(\Gesdon\Utils\Currency::convertCurrency($row['montant']));
          $don->setVia('TPE0829374');
          $don->setMoyenPaiement(DonPeer::CARTE_BANCAIRE);
          $don->setStatutPaiement(DonPeer::STATUT_OK);
          $don->setDatePaiement($row['date']);
          $don->setFrais(0);
          $don->save();
        }
        
        // Création des informations complémentaires
        $info = new CmcicInfo();
        $info->setDonateur($donateur);
        $info->setCvx($row['cvx']);
        $info->setValiditeCarte($row['vld']);
        $info->setBrand($row['brand']);
        $info->setStatus3ds($row['status3ds']);
        $info->setMotifRefus($row['motif_refus']);
        $info->setRecouvrement($row['recouvrement']);
        $info->setLibRecouvrement($row['lib_recouvrement']);
        $info->setAnnulation($row['annulation']);
        $info->setLibAnnulation($row['lib_annulation']);
        $info->setDateAnnulation($row['date_annulation']);
        $info->save();
        
        $this->getConnection()->commit();
      } catch (\Exception $e) {
        $this->getConnection()->rollBack();
        $this->logSection(__METHOD__, 'Erreur dans le traitement de la ligne '.$row['id'].' - '.$e->getMessage(), BaseTask::ERROR);
      }
    }
  }
  
  
  /**
   * Traitement du fichier CSV pour les paiements récurrents du CMCIC
   *
   * @access  private
   */
  private function processCmcicRecurrent()
  {
    $filename = \Gesdon\Core\Config::get('data_dir').DIRECTORY_SEPARATOR.'recurrents.csv';
    
    $handle = fopen($filename, 'rb');
    if ($handle === false) {
      $this->logSection(__METHOD__, 'Impossible d\'ouvrir le fichier '.$filename, BaseTask::ERROR);
      return;
    }
    
    $i = 0;
    while ($datas = fgetcsv($handle, 1000, ',', '"')) {
      $this->logSection(__METHOD__, 'Traitement de la ligne '.(++$i));
      try {
        $don = new Don();
        $don->setIdentPaiement($datas[1]);
        $don->setMontant(\Gesdon\Utils\Currency::convertCurrency($datas[2]));
        $don->setVia('TPE0829376');
        $don->setMoyenPaiement(DonPeer::CARTE_BANCAIRE);
        $don->setStatutPaiement(DonPeer::STATUT_OK);
        $don->setDatePaiement(preg_replace('/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})/', '\3-\2-\1', $datas[0]));
        $don->setFrais(0);
        $don->save();
      } catch (\Exception $e) {
        $this->logSection(__METHOD__, 'Erreur dans le traitement de la ligne '.$i.' - '.$e->getMessage(), BaseTask::ERROR);
      }
    }
  }
  
  
  /**
   * Traitement des données de paypal via un CSV
   *
   * @access  private
   */
  private function processPaypal()
  {
    $filename = \Gesdon\Core\Config::get('data_dir').DIRECTORY_SEPARATOR.'paypal.csv';
    
    $handle = fopen($filename, 'rb');
    if ($handle === false) {
      $this->logSection(__METHOD__, 'Impossible d\'ouvrir le fichier '.$filename, BaseTask::ERROR);
      return;
    }
    
    $available_type = array(
      'Paiement d\'abonnement reçu',
      'Don reçu',
    );
    $i = 0;
    while ($datas = fgetcsv($handle, 1000, ',', '"')) {
      $this->logSection(__METHOD__, 'Traitement de la ligne '.(++$i));
      
      foreach ($datas as $key => $value) {
        if (\Gesdon\Utils\sfStringPP::isUtf8($value) === false) {
          $datas[$key] = utf8_encode($value);
        }
      }
      
      try {
        if (in_array($datas[4], $available_type) === false) {
          // On ne traite que les dons
          $this->logSection(__METHOD__, 'On ne traite par la ligne (pas un don) '.($i).' : '.$datas[4]);
          continue;
        }
        
        if ($datas[5] !== 'Terminé') {
          // On ne traite que les dons terminés
          $this->logSection(__METHOD__, 'On ne traite par la ligne (non terminé) '.($i).' : '.$datas[5]);
          continue;
        }
        
        if ($datas[7] !== 'EUR') {
          // On ne traite pas les paiements qui ne sont pas en euros
          $this->logSection(__METHOD__, 'On ne traite par la ligne (pas en euros) '.($i).' : '.$datas[7]);
          continue;
        }
        
        $this->getConnection()->beginTransaction();
        
        $montant = \Gesdon\Utils\Currency::convertCurrency(trim($datas[8]));
        $frais = \Gesdon\Utils\Currency::convertCurrency(trim($datas[9]));
        $date = preg_replace('/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})/', '\3-\2-\1', trim($datas[0]));
        $nom = trim($datas[3]);
        $ident_paiement = trim($datas[14]);
        $email = trim($datas[12]);
        $subscr_id = trim($datas[34]);
        $rue = trim($datas[38]);
        $cp = trim($datas[42]);
        $ville = trim($datas[40]);
        $pays = trim($datas[43]);
        $item_name = trim($datas[19]);
        $item_number = trim($datas[20]);
        
        if (empty($subscr_id) === false) {
          // C'est un paiement d'abonement, il faut récupère les données dans l'ancienne base de données
          list($email, $rue, $cp, $ville, $pays) = $this->getOldPaypal($subscr_id);
          
          // On permute subscr_id et ident_paiement
          list($subscr_id, $ident_paiement) = array($ident_paiement, $subscr_id);
        }
        
        // Création du donateur
        $donateur = DonateurQuery::create()->filterByIdentPaiement($ident_paiement)->findOneOrCreate();
        $donateur->setNom($nom);
        $donateur->setEmail($email);
        $donateur->setRue($rue);
        $donateur->setCp($cp);
        $donateur->setVille($ville);
        $donateur->setpays($pays);
        $donateur->setIdentPaiement($ident_paiement);
        if ($donateur->getDateCreation() === null) {
          $donateur->setDateCreation($date);
        }
        $donateur->setTypeDonateur(DonateurPeer::PARTICULIER);
        $donateur->save();
        
        // Création du don
        $don = new Don();
        $don->setIdentPaiement($ident_paiement);
        $don->setMontant($montant);
        $don->setVia('Paypal');
        $don->setMoyenPaiement(DonPeer::CARTE_BANCAIRE);
        $don->setStatutPaiement(DonPeer::STATUT_OK);
        $don->setDatePaiement($date);
        $don->setFrais(($frais < 0) ? $frais * -1 : $frais);
        $don->save();
        
        // Création des infos supplémentaires
        $info = new PaypalInfo();
        $info->setDon($don);
        $info->setItemName($item_name);
        $info->setItemNumber($item_number);
        $info->setReference(empty($subscr_id) ? $ident_paiement : $subscr_id);
        $info->save();
        
        $this->getConnection()->commit();
      } catch (\Exception $e) {
        $this->logSection(__METHOD__, 'Erreur dans le traitement de la ligne '.$i.' - '.$e->getMessage(), BaseTask::ERROR);
        $this->getConnection()->rollBack();
      }
    }
  }
  
  
  /**
   * Retourne les infos sur la personne ayant souscrit un abonnement paypal
   *
   * @param   string  $subscr_id  l'identifiant de souscription
   * @access  private
   */
  private function getOldPaypal($subscr_id)
  {
    $sql = 'SELECT * FROM zz_paypal_subscription_info WHERE subscr_id = :subscr_id AND sub_event = \'subscr_signup\'';
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->bindValue(':subscr_id', $subscr_id, \PDO::PARAM_STR);
    
    if ($stmt->execute() === false) {
      throw new \Exception('impossible de récupérer les données pour le subscr_id (execute) : '.$subscr_id);
    }
    
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    if ($row === false) {
      throw new \Exception('impossible de récupérer les données pour le subscr_id (fetch) : '.$subscr_id);
    }
    
    return array(
      $row['subscriber_emailaddress'],
      $row['street'],
      $row['zipcode'],
      $row['city'],
      $row['country'],
    );
  }
  
  
  /**
   * Traitement des données concernant les chèques (import par CSV)
   *
   * @access  private
   */
  private function processCheques()
  {
    $filename = \Gesdon\Core\Config::get('data_dir').DIRECTORY_SEPARATOR.'cheques.csv';
    
    $handle = fopen($filename, 'rb');
    if ($handle === false) {
      $this->logSection(__METHOD__, 'Impossible d\'ouvrir le fichier '.$filename, BaseTask::ERROR);
      return;
    }
    
    $i = 0;
    while ($datas = fgetcsv($handle, 1000, ',', '"')) {
      $this->logSection(__METHOD__, 'Traitement de la ligne '.(++$i));
      
      try {
        if (isset($datas[8])) {
          $special = trim($datas[8]);
          if (empty($special) === false) {
             // On ne traite pas les paiements qui ne sont pas en euros
            $this->logSection(__METHOD__, 'On ne traite par la ligne '.($i).' : '.$special);
            continue;
          }
        }
        
        $this->getConnection()->beginTransaction();
        
        $nom = trim($datas[0]);
        $prenom = trim($datas[1]);
        $rue = trim($datas[2]);
        $cp = trim($datas[3]);
        $ville = trim($datas[4]);
        $pays = 'France';
        $email = trim($datas[5]);
        $date = preg_replace('/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})/', '\3-\2-\1', trim($datas[7]));
        $montant = \Gesdon\Utils\Currency::convertCurrency(trim($datas[6]));
        $ident_paiement = 'CHQ_'.sha1(rand(0, 9999999).uniqid().microtime());
        
        $donateur = new Donateur();
        $donateur->setNom($nom);
        $donateur->setPrenom($prenom);
        $donateur->setEmail($email);
        $donateur->setRue($rue);
        $donateur->setCp($cp);
        $donateur->setVille($ville);
        $donateur->setpays($pays);
        $donateur->setIdentPaiement($ident_paiement);
        $donateur->setDateCreation($date);
        $donateur->setTypeDonateur(DonateurPeer::PARTICULIER);
        $donateur->save();
        
        // Création du don
        $don = new Don();
        $don->setIdentPaiement($ident_paiement);
        $don->setMontant($montant);
        $don->setVia(DonPeer::CHEQUE);
        $don->setMoyenPaiement(DonPeer::CHEQUE);
        $don->setStatutPaiement(DonPeer::STATUT_OK);
        $don->setDatePaiement($date);
        $don->setFrais(0);
        $don->save();
        
        $this->getConnection()->commit();
      } catch (\Exception $e) {
        $this->logSection(__METHOD__, 'Erreur dans le traitement de la ligne '.$i.' - '.$e->getMessage(), BaseTask::ERROR);
        $this->getConnection()->rollBack();
      }
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
      $this->con = \Propel::getConnection(DonPeer::DATABASE_NAME, \Propel::CONNECTION_WRITE);
    }
    
    return $this->con;
  }
}