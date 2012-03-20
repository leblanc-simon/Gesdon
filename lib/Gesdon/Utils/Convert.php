<?php

namespace Gesdon\Utils;

use Gesdon\Core\Exception;
use Gesdon\Database\Don;
use Gesdon\Database\DonPeer;
use Gesdon\Database\DonQuery;
use Gesdon\Database\Donateur;
use Gesdon\Database\DonateurPeer;
use Gesdon\Database\DonateurQuery;
use Gesdon\Database\RecuFiscal;
use Gesdon\Database\RecuFiscalPeer;
use Gesdon\Database\RecuFiscalQuery;
use Gesdon\Database\RecuFiscalHasDon;
use Gesdon\Database\RecuFiscalHasDonPeer;
use Gesdon\Database\RecuFiscalHasDonQuery;

class Convert
{
  /**
   * Converti un donateur sur une période donnée en reçu fiscal
   *
   * @param   \Gesdon\Database\Donateur   $donateur     Le donateur recherché
   * @param   \DateTime                   $debut        La date de début de la recherche
   * @param   \DateTime                   $fin          La date de fin de la recherche
   * @return  \Gesdon\Database\RecuFiscal               Le reçu fiscal correspondant
   */
  static public function donnateurToRecuFical(Donateur $donateur, \DateTime $debut, \DateTime $fin)
  {
    try {
      $con = \Propel::getConnection(DonateurPeer::DATABASE_NAME, \Propel::CONNECTION_WRITE);
      $con->beginTransaction();
      
      $recu_fiscal = new RecuFiscal();
      $recu_fiscal->setNumero(Convert::getNextNumero());
      $recu_fiscal->setDateCreation(new \DateTime());
      $recu_fiscal->setNom($donateur->getNom());
      $recu_fiscal->setPrenom($donateur->getPrenom());
      $recu_fiscal->setEmail($donateur->getEmail());
      $recu_fiscal->setRue($donateur->getRue());
      $recu_fiscal->setCp($donateur->getCp());
      $recu_fiscal->setVille($donateur->getVille());
      $recu_fiscal->setPays($donateur->getPays());
      $recu_fiscal->setMontant(Convert::getMontant($donateur, $debut, $fin));
      $recu_fiscal->setMoyenPaiement(Convert::getMoyenPaiement($donateur, $debut, $fin));
      $recu_fiscal->setDateDonDebut($debut);
      $recu_fiscal->setDateDonFin($fin);
      $recu_fiscal->setRecurrent(Convert::isRecurrent($donateur, $debut, $fin));
      $recu_fiscal->setFilename(sha1(rand(0, 999999).microtime().rand(0, 999999)).'.pdf');
      $recu_fiscal->save($con);
      
      $dons = DonQuery::create()->filterByDonateurAndDate($donateur, $debut, $fin)->find();
      foreach ($dons as $don) {
        $relation = RecuFiscalHasDonQuery::create()->filterByRecuFiscal($recu_fiscal)->filterByDon($don)->findOneOrCreate();
        $relation->save();
      }
      
      $con->commit();
    } catch (\Exception $e) {
      $con->rollBack();
      throw $e;
    }
    
    return $recu_fiscal;
  }
  
  /**
   * Retourne le prochain numéro de reçu fiscal
   */
  static private function getNextNumero()
  {
    $numero = RecuFiscalQuery::create()->orderByNumero(\Criteria::DESC)->findOne();
    if ($numero === null) {
      return \Gesdon\Core\Config::get('next_num_fiscal', 1);
    }
    
    return $numero->getNumero() + 1;
  }
  
  
  /**
   * Récupère le montant total des dons pour un donateur et une période
   *
   * @param   \Gesdon\Database\Donateur   $donateur     Le donateur recherché
   * @param   \DateTime                   $debut        La date de début de la recherche
   * @param   \DateTime                   $fin          La date de fin de la recherche
   * @return  float                                     Le montant total des dons
   * @access  private
   * @static
   */
  static private function getMontant(Donateur $donateur, \DateTime $debut, \DateTime $fin)
  {
    $dons = DonQuery::create()->filterByDonateurAndDate($donateur, $debut, $fin)->find();
    
    $montant = 0;
    foreach ($dons as $don) {
      $montant += $don->getMontant();
    }
    
    return $montant;
  }
  
  
  /**
   * Retourne le moyen de paiement utilisé par le donateur sur une période donnée
   * 
   * @param   \Gesdon\Database\Donateur   $donateur     Le donateur recherché
   * @param   \DateTime                   $debut        La date de début de la recherche
   * @param   \DateTime                   $fin          La date de fin de la recherche
   * @return  string|null                               Moyen de paiement utilisé ou null si aucun don
   * @access  private
   * @static
   */
  static private function getMoyenPaiement(Donateur $donateur, \DateTime $debut, \DateTime $fin)
  {
    $dons = DonQuery::create()->filterByDonateurAndDate($donateur, $debut, $fin)->findOne();
    if ($dons !== null) {
      return $dons->getMoyenPaiement();
    }
    
    return null;
  }
  
  
  /**
   * Indique si les dons pour un donateur et une période sont récurrents ou non
   *
   * @param   \Gesdon\Database\Donateur   $donateur     Le donateur recherché
   * @param   \DateTime                   $debut        La date de début de la recherche
   * @param   \DateTime                   $fin          La date de fin de la recherche
   * @return  bool                                      Vrai si c'est un donateur récurrent, faux sinon
   * @access  private
   * @static
   */
  static private function isRecurrent(Donateur $donateur, \DateTime $debut, \DateTime $fin)
  {
    $nb_dons = DonQuery::create()->filterByDonateurAndDate($donateur, $debut, $fin)->count();
    if ($nb_dons <= 1) {
      return false;
    }
    
    return true;
  }
}