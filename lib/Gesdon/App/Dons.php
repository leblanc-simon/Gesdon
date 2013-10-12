<?php

namespace Gesdon\App;

use Gesdon\Core\Config;
use Gesdon\Database\Don as DBDon;
use Gesdon\Database\DonPeer;
use Gesdon\Database\DonQuery;
use Gesdon\Core\Exception;

class Dons extends Main
{
    public function executeGet()
    {
        $page = (int)$this->request->get('page', 1);
        if ($page === 0) {
            $page = 1;
        }
        
        $query = DonQuery::create()->orderByDatePaiement(\Criteria::DESC);
        
        if ('' !== $reference = $this->session->get('search_reference', '')) {
            $query->filterByIdentPaiement($reference);
        }
        
        if ('' !== $name = $this->session->get('search_name', '')) {
            $query
                ->useDonateurQuery()
                    ->filterByNom('%'.$name.'%')
                    ->_or()
                    ->filterByPrenom('%'.$name.'%')
                ->endUse();
        }
        
        if (null !== $begin = $this->session->get('search_begin', '')) {
            $query->filterByDatePaiement(array('min' => $begin));
        }
        
        if (null !== $end = $this->session->get('search_end', '')) {
            $query->filterByDatePaiement(array('max' => $end));
        }
        
        if ('' !== $email = $this->session->get('search_email', '')) {
            $query
                ->useDonateurQuery()
                    ->filterByEmail($email)
                ->endUse();
        }
        
        $dons = $query->paginate($page, Config::get('pagination_nb_item', 20));
        
        $form = $this->getForm();
        
        return $this->render(array('dons' => $dons, 'form' => $form->createView(), 'page' => $page));
    }
    
    
    public function executePost()
    {
        $form = $this->getForm();
        
        $form->bind($this->request);
        
        if ($form->isValid()) {
            $form = $form->getData();
            if (is_array($form) === false) {
                throw new Exception('form must be an array');
            }
            
            $name = isset($form['name']) ? trim((string)$form['name']) : '';
            $reference = isset($form['reference']) ? trim((string)$form['reference']) : '';
            $email = isset($form['email']) ? trim((string)$form['email']) : '';
            $begin = isset($form['begin']) ? $form['begin'] : null;
            $end = isset($form['end']) ? $form['end'] : null;
            $this->session->set('search_name', $name);
            $this->session->set('search_reference', $reference);
            $this->session->set('search_email', $email);
            $this->session->set('search_begin', $begin);
            $this->session->set('search_end', $end);
        }
        
        return $this->getApp()->redirect('dons');
    }
    
    
    protected function getForm()
    {
        return $this->form
                        ->createBuilder('form')
                        ->add('name', 'text', array(
                            'data' => $this->session->get('search_name'),
                            'required' => false,
                            'label' => 'Nom du donateur',
                        ))
                        ->add('email', 'email', array(
                            'data' => $this->session->get('search_email'),
                            'required' => false,
                            'label' => 'Adresse e-mail',
                        ))
                        ->add('begin', 'date', array(
                            'data' => $this->session->get('search_begin'),
                            'required' => false,
                            'label' => 'Date de début',
                            'widget' => 'single_text'
                        ))
                        ->add('end', 'date', array(
                            'data' => $this->session->get('search_end'),
                            'required' => false,
                            'label' => 'Date de fin',
                            'widget' => 'single_text'
                        ))
                        ->add('reference', 'text', array(
                            'data' => $this->session->get('search_reference'),
                            'required' => false,
                            'label' => 'Référence de paiement',
                        ))
                        ->getForm();
    }
}