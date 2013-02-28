<?php

namespace Gesdon\Database;

use Gesdon\Database\om\BaseRecuFiscal;


/**
 * Skeleton subclass for representing a row from the 'recu_fiscal' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.Gesdon.Database
 */
class RecuFiscal extends BaseRecuFiscal
{
  /**
   * Retourne le montant au format texte
   *
   * @return  string    le montant au format texte
   * @access  public
   */
  public function getMontantTexte()
  {
    $don = (string)$this->getMontant();
    $don = explode('.', $don);
    
    $return_value = trim(\Gesdon\Utils\SpellNumber::NumberToText($don[0])).' euros';
    if (isset($don[1]) && $don[1] != '00') {
      $return_value .= ' et '.trim(\Gesdon\Utils\SpellNumber::NumberToText($don[1])).' cents';
    }
    
    return $return_value;
  }
  
  
  public function getDateDonTexte()
  {
    if ($this->getRecurrent() === true) {
      return $this->getDateDonDebut('Y');
    } else {
      $don = \Gesdon\Database\DonQuery::create()->filterByRecuFiscal($this)->findOne();
      if ($don !== null) {
        return $don->getDatePaiement('d/m/Y');
      }
    }
    
    return null;
  }
} // RecuFiscal
