<?php

namespace Gesdon\Task;

use Gesdon\Core\Exception;
use Gesdon\Core\Config;
use Gesdon\Database\Don;
use Gesdon\Database\DonPeer;
use Gesdon\Database\DonQuery;
use Gesdon\Database\Donateur;
use Gesdon\Database\DonateurPeer;
use Gesdon\Database\DonateurQuery;
use Gesdon\Database\CmcicInfo;
use Gesdon\Database\CmcicInfoPeer;
use Gesdon\Database\CmcicInfoQuery;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Revival extends BaseTask
{
    protected $relances = array(
        1 => 30, // Première relance 30 jours avant
        2 => 15, // Deuxième relance 15 jours avant
        3 => 0,  // Troisième relance le jour de l'expiration
    );
    
    /**
     * Configuration de la tâche
     */
    protected function configure()
    {
        $this
            ->setName('send:revival')
            ->setDescription('Relance des donateurs pour lesquels la carte bancaire expire')
            ->addOption('day', null, InputOption::VALUE_NONE, '', null);
    }
  
  
    /**
     * Execution de la tâche
     *
     * @param   \Symfony\Component\Console\Input\InputInterface   $input  les entrées de la console
     * @param   \Symfony\Component\Console\Output\OutputInterface $output les sortie de la console
     * @access  protected
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setOutput($output);
        
        $this->sendFirstRevival();
    }
    
    
    /**
     * Envoie les premières relances pour les cartes qui arrivent à expiration
     *
     * @access  private
     */
    private function sendFirstRevival()
    {
        $cmcics = CmcicInfoQuery::create()
                    ->filterByValiditeCarte($this->generateRegexDate(), ' REGEXP ')
                    ->filterByNbRelance(0)
                    ->_or()
                    ->filterByNbRelance(null, \Criteria::ISNULL)
                    ->filterByAnnulation(false)
                    ->filterByRecouvrement(false)
                    ->useDonateurQuery()
                    ->endUse()
                    ->with('Donateur')
                    ->find();
        
        foreach ($cmcics as $cmcic) {
            $this->sendRevival($cmcic, 1);
        }
    }
    
    
    /**
     * Envoi du mail de relance
     *
     * @param   CmcicInfo   $cmcic      L'objet correspondant au paiement bancaire
     * @param   int         $revival    Le numéro de la relance
     * @access  private
     */
    private function sendRevival(CmcicInfo $cmcic, $revival)
    {
        $params = array(
            'nom' => $cmcic->getDonateur()->getNom(),
            'prenom' => $cmcic->getDonateur()->getPrenom(),
            'date_paiement' => $cmcic->getDonateur()->getFirstDon()->getDatePaiement(),
            'date_expiration' => $cmcic->getValiditeCarte(),
        );
        
        $loader = new \Twig_Loader_Filesystem(Config::get('template_dir'));
        $twig = new \Twig_Environment($loader, array(
            'cache' => false,
        ));
        
        $this->log($twig->render('mail/revival'.$revival.'.html.twig', $params));
    }
    
    
    /**
     * Génére la regex à utiliser en fonction de la relance
     *
     * @return  string                  La regex à utiliser
     */
    private function generateRegexDate()
    {
        $current_date = new \DateTime();
        $months = array();
        
        for ($i = 0; $i < 3; $i++) {
            $current_date = $current_date->sub(new \DateInterval('P1M'));
            $months[] = $current_date->format('my');
        }
        
        $regexp = '^('.implode('|', $months).')$';
        
        return $regexp;
    }
}