<?php

namespace Gesdon\Database;

use Gesdon\Database\om\BaseDonPeer;


/**
 * Skeleton subclass for performing query and update operations on the 'don' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.Gesdon.Database
 */
class DonPeer extends BaseDonPeer
{
  const CARTE_BANCAIRE  = 'Carte bancaire';
  const CHEQUE          = 'Chèque';
  const VIREMENT        = 'Virement';
  const ESPECE          = 'Espèce';
  
  const STATUT_OK       = 'ok';
  const STATUT_NOK      = 'nok';
} // DonPeer
