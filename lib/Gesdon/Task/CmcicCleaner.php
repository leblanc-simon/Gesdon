<?php
namespace Gesdon\Task;

use Gesdon\Core\Config;
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


/**
 *
 * @author leviathan
 *        
 */
class CmcicCleaner extends BaseTask
{

  /**
   * Connexion PDO
   * 
   * @var PDO
   * @access private
   */
  private $con = null;
  
  /**
   * Liste des paiements annulés par la tâche
   * 
   * @var     array
   * @access  private
   */
  private $ident_paiements = array();
  
  /**
   * L'object \Cmcic permettant de requêter le site
   * 
   * @var     \Cmcic
   * @access  private
   */
  private $cmcic = null;


  /**
   * Constructeur
   *
   * @param array $args Les arguments de la tâche
   * @access public
   * @see BaseTask::__construct
   */
  public function __construct ($args = array())
  {
    parent::__construct($args);
  }


  /**
   * Lancement de la tâche
   *
   * @access public
   */
  public function run ()
  {
    $this->logSection(__METHOD__, 'begin');
    
    $this->getRecurrentYear();
    $this->deleteRecurrent();
    
    $this->logSection(__METHOD__, 'end');
  }
  
  
  /**
   * Récupère les donateurs récurrents ayant donné 12 fois au moins
   * 
   * @return   array   L'identifiant de paiement à annuler
   * @access   private
   */
  private function getRecurrentYear()
  {
    $this->logSection(__METHOD__, 'begin');
    
    $ident_paiements = $this->getCmcic()->getPaymentsWithMore(Config::get('cmcic_nb_month'));
    
    if ($ident_paiements === false) {
      throw new Exception('Erreur lors de la récupération des donateurs récurrent de plus d\'un an : '.print_r($this->getCmcic()->getErrors(), true));
    }
    
    $this->ident_paiements = $ident_paiements;
    
    $this->logSection(__METHOD__, 'end');
    return $this->ident_paiements;
  }
  
  
  /**
   * Supprime les paiements récurrents depuis plus d'un an
   * 
   * @access   private
   */
  private function deleteRecurrent()
  {
    $this->logSection(__METHOD__, 'begin');
    
    foreach ($this->ident_paiements as $ident_paiement) {
      $this->logSection(__METHOD__, 'Traitement du donateur : '.$ident_paiement);
      $result = $this->getCmcic()->cancelPayment($ident_paiement);
      if ($result === true) {
        $this->markAsDeleted($ident_paiement);
      } else {
        $this->logSection(__METHOD__, 'Erreur lors de la suppression du paiement : '.$ident_paiement, 'ERROR');
      }
    }
    
    $this->logSection(__METHOD__, 'end');
  }
  
  
  /**
   * Marque un donateur comme ayant été supprimé
   * 
   * @param string   $ident_paiement   L'identifiant de paiement annulé
   * @return bool                      Vrai en cas de succès, faux sinon
   * @access   private
   */
  private function markAsDeleted($ident_paiement)
  {
    $this->logSection(__METHOD__, 'begin');
    
    $donateur = DonateurQuery::create()->filterByIdentPaiement($ident_paiement)->findOne();
    if ($donateur === null) {
      // Le donateur n'existe pas dans la base c'est un soucis
      $this->logSection(__METHOD__, 'Impossible de trouver un donateur pour '.$ident_paiement, 'ERROR');
      return false;
    }
    
    $infos = $donateur->getCmcicInfos();
    if (count($infos) !== 1) {
      // Il n'y a pas un nombre cohérent d'information
      $this->logSection(__METHOD__, 'Nombre incohérent d\'information sur le client : '.count($infos), 'ERROR');
      return false;
    }
    
    $info = $infos[0];
    
    $info->setAnnulation(true);
    $info->setDateAnnulation(new \DateTime());
    $info->setLibAnnulation('EXPIRATION AUTO 12 MOIS');
    $info->save($this->getConnection());
    
    $this->logSection(__METHOD__, 'end');
    return true;
  }
  
  
  /**
   * Récupère l'objet Cmcic permettant de faire les requêtes sur le site
   * 
   * @return   \Cmcic     L'object \Cmcic
   * @access   private
   */
  private function getCmcic()
  {
    if ($this->cmcic === null) {
      \sfConfig::set('cmcic_login', Config::get('cmcic_login'));
      \sfConfig::set('cmcic_pass', Config::get('cmcic_pass'));
      \sfConfig::set('cmcic_tpe', Config::get('cmcic_tpe'));
      
      $options = array(
        'verbose'      => false,
        'verbose_log'  => false,
      );
      
      $this->cmcic = getCmcic($options);
    }
    
    return $this->cmcic;
  }


  /**
   * Récupère une connexion à la base de données
   *
   * @return PDO une connexion à la base de données
   * @access private
   */
  private function getConnection ()
  {
    if ($this->con === null) {
      $this->con = \Propel::getConnection(DonPeer::DATABASE_NAME, \Propel::CONNECTION_WRITE);
    }
    
    return $this->con;
  }
}