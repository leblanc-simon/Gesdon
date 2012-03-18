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
  private $con = null;
  
  public function __construct($args = array())
  {
    parent::__construct($args);
  }
  
  
  public function run()
  {
    $this->processCmcic();
    $this->processCmcicRecurrent();
  }
  
  
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
          $don->setMontant($row['montant']);
          $don->setVia('TPE0829374');
          $don->setMoyenPaiement(DonPeer::CARTE_BANCAIRE);
          $don->setStatutPaiement(DonPeer::STATUT_OK);
          $don->setDatePaiement($row['date']);
          $don->setFrais(0);
          $don->save();
          
          // Création des informations complémentaires
          //$cmcic_info = new CmcicInfo();
          
        }
        
        $this->getConnection()->commit();
      } catch (\Exception $e) {
        $this->getConnection()->rollBack();
        $this->logSection(__METHOD__, 'Erreur dans le traitement de la ligne '.$row['id'], BaseTask::ERROR);
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
        $don->setMontant($datas[2]);
        $don->setVia('TPE0829376');
        $don->setMoyenPaiement(DonPeer::CARTE_BANCAIRE);
        $don->setStatutPaiement(DonPeer::STATUT_OK);
        $don->setDatePaiement(preg_replace('/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})/', '\3-\2-\1', $datas[0]));
        $don->setFrais(0);
        $don->save();
      } catch (\Exception $e) {
        $this->logSection(__METHOD__, 'Erreur dans le traitement de la ligne '.$i, BaseTask::ERROR);
      }
    }
  }
  
  
  private function processPaypal()
  {
    
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