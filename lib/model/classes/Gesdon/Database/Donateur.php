<?php

namespace Gesdon\Database;

use Gesdon\Database\om\BaseDonateur;

/**
 * Skeleton subclass for representing a row from the 'donateur' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.Gesdon.Database
 */
class Donateur extends BaseDonateur
{
    public function getFirstDon()
    {
        return DonQuery::create()
                ->filterByIdentPaiement($this->getIdentPaiement())
                ->orderByDatePaiement()
                ->findOne();
    }
} // Donateur
