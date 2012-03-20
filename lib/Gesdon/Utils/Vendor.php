<?php

namespace Gesdon\Utils;

use Gesdon\Core\Config;

class Vendor
{
  /**
   * Registers Gesdon\Utils\Vendor as an SPL autoloader.
   */
  static public function register()
  {
    spl_autoload_register(__CLASS__.'::autoloader', true);
  }
  
  /**
   * Méthode gérant l'autoload des classes du namespace vendor de Gesdon
   */
  static public function autoloader($class)
  {
    if ($class === 'FPDF') {
      require_once Config::get('vendor_dir').DIRECTORY_SEPARATOR.'fpdf'.DIRECTORY_SEPARATOR.'fpdf.php';
    }
  }
}