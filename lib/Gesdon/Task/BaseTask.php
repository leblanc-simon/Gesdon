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
  
  protected function log($message, $type = BaseTask::INFO)
  {
    echo get_called_class().' - '.date('Y-m-d H:i:s')."\t".'['.$type.']'."\t".$message."\n";
  }
}