<?php

namespace Gesdon\Task;

use Gesdon\Core\Config;
use Gesdon\Core\Exception;

use OpenCmcicAction\Core\Config as ConfigCmcic;
use OpenCmcicAction\Request\Paiement as PaiementCmcic;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateBuildRecurrent extends BaseTask
{
    private $debut  = null;
    private $fin    = null;
    
    /**
     * Configuration de la tâche
     */
    protected function configure()
    {
        $this
            ->setName('migrate:build-recurrent')
            ->setDescription('Construit le fichier CSV à importer dans Gesdon pour les dons récurrent du CMCIC')
            ->addArgument('debut',
                          InputArgument::REQUIRED,
                          'Date de début pour la recherche des dons'
            )
            ->addArgument('fin',
                          InputArgument::REQUIRED,
                          'Date de fin pour la recherche des dons'
            );
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
        
        $debut  = $input->getArgument('debut');
        $fin    = $input->getArgument('fin');
        
        if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $debut) === 0 || preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $fin) === 0) {
            throw new Exception('Date de début et date de fin doivent être au format anglais : yyyy-mm-dd');
        }
        
        $this->debut = new \DateTime($debut);
        $this->fin = new \DateTime($fin);
        
        // Si l'utilisateur a inversé début et fin, on corrige automatiquement (oui, l'utilisateur est con par défaut :-))
        if ($this->debut > $this->fin) {
            list($this->debut, $this->fin) = array($this->fin, $this->debut);
        }
        
        // On place la fin à 23:59:59 pour récupérer tous les dons (sans oublier la dernière journée)
        $this->fin->setTime(23, 59, 59);
        
        // On lance la tâche
        $this->log('Lancement de la tâche');
        
        $this->setConfig();
        $this->getDatas();
        
        $this->log('Fin du traitement de la tâche');
    }
    
    /**
     * Paramétrage de la configuration du module OpenCmcicAction
     */
    private function setConfig()
    {
        $this->log('begin');
        
        ConfigCmcic::add(array(
            'cmcic_version' => '3.0',
            'cmcic_server' => 'https://paiement.creditmutuel.fr/',
            'cmcic_key' => Config::get('cmcic_key'),
            'cmcic_tpe' => Config::get('cmcic_tpe'),
            'cmcic_company_code' => Config::get('cmcic_company_code'),
            'cmcic_url_ok' => 'http://soutenir.framasoft.org/merci',
            'cmcic_url_ko' => 'http://soutenir.framasoft.org/echec',
            
            'cmcic_web_server' => 'https://www.cmcicpaiement.fr/fr/',
            'cmcic_web_username' => Config::get('cmcic_login'),
            'cmcic_web_password' => Config::get('cmcic_pass'),
            
            'log_dir' => Config::get('base_dir').DIRECTORY_SEPARATOR.'log',
        ));
        
        $this->log('end');
    }
    
    
    private function getDatas()
    {
        $this->log('begin');
        
        $filename = Config::get('data_dir').DIRECTORY_SEPARATOR.'recurrents.csv';
        
        $request = new PaiementCmcic($this->debut, $this->fin);
        $paiements = $request->process();
        
        $csv = '';
        foreach ($paiements as $paiement) {
            $line = array();
            $line[] = substr($paiement['date'], 0, 10);
            $line[] = $paiement['reference'];
            $line[] = $paiement['amount'];
            
            if (empty($csv) === false) {
                $csv .= "\n";
            }
            $csv .= implode(',', $line);
        }
        
        if (file_put_contents($filename, $csv) === false) {
            $this->log('Error while write '.$filename, 'error');
        }
        
        $this->log('end');
    }
}