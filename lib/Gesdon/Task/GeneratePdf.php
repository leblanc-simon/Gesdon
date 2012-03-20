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
use Gesdon\Database\RecuFiscalHasDonQuery;
use Gesdon\Utils\RecuPdf;

class GeneratePdf extends BaseTask
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
  }
  
  
  /**
   * Lancement de la tâche
   *
   * @access  public
   */
  public function run()
  {
    $recu_fiscals = RecuFiscalQuery::create()->find();
    
    foreach ($recu_fiscals as $recu_fiscal) {
      $recu_pdf = new RecuPdf();
      $recu_pdf->init($recu_fiscal);
      $recu_pdf->generatePDF(true);
    }
  }
}