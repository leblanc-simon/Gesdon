<?php

namespace Gesdon\Core;

class Autoload
{
  /**
   * Registers Gesdon\Core\Autoload as an SPL autoloader.
   */
  static public function register()
  {
    spl_autoload_register(__CLASS__.'::autoloader', true);
  }
  
  
  /**
   * Méthode gérant l'autoload des classes du namespace Gesdon
   */
  static public function autoloader($class)
  {
    // Si ce n'est pas Gesdon, on ne s'en occupe pas
    if (strpos($class, 'Gesdon') !== 0) {
      return;
    }
    
    if (strpos($class, 'Gesdon\\App\\') === 0) {
      // Si c'est Gesdon\App, alors c'est le répertoire app
      $directory = Config::get('app_dir');
    } elseif (strpos($class, 'Gesdon\\Database\\') === 0) {
      self::autoloaderModel($class);
      return;
    } else {
      // Sinon c'est le répertoire lib
      $directory = Config::get('lib_dir');
    }
    
    // On inclu le fichier
    $parts = explode('\\', $class);
    if (count($parts) < 2) {
      return;
    }
    
    self::includeFile($directory, $parts);
  }
  
  
  static private function autoloaderModel($class)
  {
    $directory = Config::get('model_dir');
    $parts = explode('\\', $class);
    
    self::includeFile($directory, $parts);
  }
  
  
  /**
   * Méthode permettant d'include le fichier requis
   */
  static private function includeFile($directory, $parts)
  {
    $filename = $directory.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $parts).'.php';
    
    if (is_file($filename) === true) {
      require_once $filename;
    }
  }
}