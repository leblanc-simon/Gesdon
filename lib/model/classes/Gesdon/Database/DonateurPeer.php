<?php

namespace Gesdon\Database;

use Gesdon\Database\om\BaseDonateurPeer;


/**
 * Skeleton subclass for performing query and update operations on the 'donateur' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.Gesdon.Database
 */
class DonateurPeer extends BaseDonateurPeer
{
  const PARTICULIER   = 'Personnel';
  const PROFESSIONNEL = 'Entreprise';
  const INCONNU       = 'undefined';
  
} // DonateurPeer
