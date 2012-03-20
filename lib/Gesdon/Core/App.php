<?php

namespace Gesdon\Core;

require_once __DIR__.DIRECTORY_SEPARATOR.'Config.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'Autoload.php';

class App
{
  static private $base_dir    = null;
  static private $app_dir     = null;
  static private $config_dir  = null;
  static private $data_dir    = null;
  static private $lib_dir     = null;
  static private $vendor_dir  = null;
  
  static public function run()
  {
    throw new Exception('Rien à voir pour le moment');
  }
  
  static public function runTask($taskname, $args = array())
  {
    self::baseRun();
    
    $task_class = 'Gesdon\\Task\\'.$taskname;
    $task = new $task_class($args);
    
    return $task->run();
  }
  
  
  static private function baseRun()
  {
    self::init();
    
    self::setup();
    
    self::autoload();
  }
  
  
  static private function init()
  {
    self::$base_dir   = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..');
    self::$app_dir    = self::$base_dir.DIRECTORY_SEPARATOR.'app';
    self::$data_dir   = self::$base_dir.DIRECTORY_SEPARATOR.'data';
    self::$config_dir = self::$base_dir.DIRECTORY_SEPARATOR.'config';
    self::$lib_dir    = self::$base_dir.DIRECTORY_SEPARATOR.'lib';
    self::$vendor_dir = self::$lib_dir.DIRECTORY_SEPARATOR.'vendor';
  }
  
  static private function setup()
  {
    // On charge d'abord les config de base pour pouvoir les utiliser dans le fichier de config utilisateur
    Config::add(array(
      'base_dir'    => self::$base_dir,
      'app_dir'     => self::$app_dir,
      'data_dir'    => self::$data_dir,
      'config_dir'  => self::$config_dir,
      'lib_dir'     => self::$lib_dir,
      'vendor_dir'  => self::$vendor_dir,
    ));
    
    $config_file = self::$config_dir.DIRECTORY_SEPARATOR.'config.php';
    if (file_exists($config_file) === false) {
      throw new \RuntimeException('Impossible de lire le fichier de configuration');
    }
    
    require_once $config_file;
  }
  
  static private function autoload()
  {
    Autoload::register();
    
    \Gesdon\Utils\Vendor::register();
    
    // Propel autoloader
    require_once self::$vendor_dir.DIRECTORY_SEPARATOR.'propel'.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'Propel.php';
    \Propel::init(self::$config_dir.DIRECTORY_SEPARATOR.'Gesdon-conf.php');
    
    // Swift autoloader
    require_once self::$vendor_dir.DIRECTORY_SEPARATOR.'swiftmailer'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'swift_required.php';
  }
}