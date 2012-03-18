#!/usr/bin/php
<?php
ini_set('display_errors', 1);
error_reporting(-1);

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'Gesdon'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'App.php';

try {

  if ($argc < 2) {
    throw new \Exception('Nombre d\'argument incorrect');
  }
  
  $task = $argv[1];
  $args = $argv;
  unset($args[0], $args[1]); // 0 : nom du script, 1: nom de la tâche
  
  $res = \Gesdon\Core\App::runTask($task, $args);
  echo $res."\n";
} catch (\Exception $e) {
  echo $e->getMessage()."\n";
  
  exit(1);
}

exit(0);