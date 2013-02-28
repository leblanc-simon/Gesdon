<?php

namespace Gesdon\Database;

require_once __DIR__.DIRECTORY_SEPARATOR.'RecuFiscalHasDonQuery.php';

use Gesdon\Database\om\BaseDonQuery;
use Gesdon\Database\RecuFiscal;
use Gesdon\Database\RecuFiscalHasDonQuery;

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
    $fin->setTime(23, 59, 59);
    return $this->filterByIdentPaiement($donateur->getIdentPaiement())
                ->filterByDatePaiement($debut, \Criteria::GREATER_EQUAL)
                ->filterByDatePaiement($fin, \Criteria::LESS_EQUAL);
  }
  
  
  public function filterByRecuFiscal(RecuFiscal $recu_fiscal)
  {
    return $this->useRecuFiscalHasDonQuery()
                  ->filterByRecuFiscal($recu_fiscal)
                ->endUse();
  }
} // DonQuery
