<?php

namespace Gesdon\Task;

use Gesdon\Core\Exception;

abstract class BaseTask
{
  const INFO  = 'INFO';
  const ERROR = 'ERROR';
  
  public function __construct($args = array())
  {
  }
  
  
  abstract public function run();
  
  protected function logSection($section, $message, $type = BaseTask::INFO)
  {
    // La plupart du temps : section == __METHOD__
    // Pour simplifier, on supprime la classe de la section : c'est crade et aléatoire mais j'assume :-)
    $section = str_replace(get_called_class().'::', '', $section);
    $this->log($section."\t".$message, $type);
  }
  
  protected function log($message, $type = BaseTask::INFO)
  {
    echo get_called_class().' - '.date('Y-m-d H:i:s')."\t".'['.$type.']'."\t".$message."\n";
  }
}