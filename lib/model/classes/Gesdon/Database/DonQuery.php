<?php

namespace Gesdon\Database;

use Gesdon\Database\om\BaseDonQuery;


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
class DonQuery extends BaseDonQuery
{
  public function filterByDonateurAndDate(Donateur $donateur, \DateTime $debut, \DateTime $fin)
  {
    return $this->filterByIdentPaiement($donateur->getIdentPaiement())
                ->filterByDatePaiement($debut, \Criteria::GREATER_EQUAL)
                ->filterByDatePaiement($fin, \Criteria::LESS_EQUAL);
  }
} // DonQuery
