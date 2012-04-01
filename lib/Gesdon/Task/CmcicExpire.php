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

class CmcicExpire extends BaseTask
{

  /**
   * Connexion PDO
   * 
   * @var PDO
   * @access private
   */
  private $con = null;
  
  /**
   * Liste des paiements dont la date d'expiration arrive à terme
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
    
    
    
    $this->logSection(__METHOD__, 'end');
  }
}