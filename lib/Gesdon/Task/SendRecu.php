<?php

namespace Gesdon\Task;

use Gesdon\Core\Exception;
use Gesdon\Database\DonPeer;
use Gesdon\Database\Donateur;
use Gesdon\Database\DonateurPeer;
use Gesdon\Database\DonateurQuery;
use Gesdon\Database\RecuFiscal;
use Gesdon\Database\RecuFiscalPeer;
use Gesdon\Database\RecuFiscalQuery;


class SendRecu extends BaseTask
{
  /**
   * Connexion PDO
   * @var     PDO
   * @access  private
   */
  private $con = null;
  
  private $debut  = null;
  private $fin    = null;
  
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
    
    if (isset($args[0]) === false || isset($args[1]) === false) {
      throw new Exception('La tâche attend 2 arguments : date de début et date de fin');
    }
    
    if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $args[0]) === 0 || preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $args[1]) === 0) {
      throw new Exception('Date de début et date de fin doivent être au format anglais : yyyy-mm-dd');
    }
    
    $this->debut = new \DateTime($args[0]);
    $this->fin = new \DateTime($args[1]);
    
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
    $sql = 'SELECT * FROM donateur WHERE ident_paiement IN'
           .'(SELECT DISTINCT(don.ident_paiement) FROM don'
           .' WHERE date_paiement >= :debut'
           .' AND date_paiement <= :fin'
           .' AND statut_paiement = :status)';
    
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->bindValue(':debut', $this->debut->format('Y-m-d'), \PDO::PARAM_STR);
    $stmt->bindValue(':fin', $this->fin->format('Y-m-d'), \PDO::PARAM_STR);
    $stmt->bindValue(':status', DonPeer::STATUT_OK, \PDO::PARAM_STR);
    
    if ($stmt->execute() === false) {
      $this->logSection(__METHOD__, 'Erreur la récupération des donateurs', BaseTask::ERROR);
      throw new Exception('impossible de finir la tâche');
    }
    
    $formater = new \PropelObjectFormatter();
    $formater->setClass('Gesdon\Database\Donateur');
    $donateurs = $formater->format($stmt);
    
    foreach ($donateurs as $donateur) {
      try {
        // Conversion du donateur en reçu fiscal
        $recu_fiscal = \Gesdon\Utils\Convert::donnateurToRecuFical($donateur, $this->debut, $this->fin);
        $this->logSection(__METHOD__, 'La conversion du donateur : '.$donateur->getId().' a été réalisée : '.$recu_fiscal->getId());
        // Génération du PDF
        
        // Envoie du PDF
      } catch (\Exception $e) {
        $this->logSection(__METHOD__, 'Erreur la conversion du donateur : '.$donateur->getId(), BaseTask::ERROR);
        continue;
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
      $this->con = \Propel::getConnection(DonateurPeer::DATABASE_NAME, \Propel::CONNECTION_WRITE);
    }
    
    return $this->con;
  }
}