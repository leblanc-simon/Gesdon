<?php

namespace Gesdon\Task;

use Gesdon\Database\DonateurPeer;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseTask extends Command
{
  /**
   * Connexion PDO
   * @var     \PDO
   * @access  protected
   */
  protected $con  = null;
  
  /**
   * Sortie sur la console
   * @var     \Symfony\Component\Console\Output\OutputInterface
   * @access  private
   */
  private $output  = null;
  
  
  /**
   * Initialise l'objet de sortie sur la console
   *
   * @param \Symfony\Component\Console\Output\OutputInterface  $output   l'objet de sortie sur la console
   */
  protected function setOutput(OutputInterface $output)
  {
    $this->output = $output;
  }
  
  
  /**
   * Récupère une connexion à la base de données
   *
   * @return  PDO   une connexion à la base de données
   * @access  protected
   */
  protected function getConnection()
  {
    if ($this->con === null) {
      $this->con = \Propel::getConnection(DonateurPeer::DATABASE_NAME, \Propel::CONNECTION_WRITE);
    }
    
    return $this->con;
  }
  
  
  /**
   * Log un message
   *
   * @param   string    $message  Le message à logger
   * @param   string    $type     Le type du message (info, error, comment, question)
   * @access  protected
   */
  protected function log($message, $type = 'info')
  {
    $backtrace = debug_backtrace();
    $method = $backtrace[1]['function'];
    $class=  isset($backtrace[1]['class']) ? $backtrace[1]['class'].'::' : '';
    
    $this->output->writeln('<'.$type.'>'.date('Y-m-d-h:i:s').' '.$class.$method.' '.$message.'</'.$type.'>');
  }
}