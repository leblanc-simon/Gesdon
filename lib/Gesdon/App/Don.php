<?php

namespace Gesdon\App;

use Gesdon\Core\Config;
use Gesdon\Database\Don as DBDon;
use Gesdon\Database\DonPeer;
use Gesdon\Database\DonQuery;
use Gesdon\Core\Exception;

class Don extends Main
{
    public function executeGet()
    {
        $id = (int)$this->request->get('id', 0);

        $this->don = DonQuery::create()->filterById($id)->findOneOrCreate();

        $this->donateur = $this->don->getDonateur();

        $form = $this->getForm();
        
        return $this->render(array('don' => $this->don, 'form' => $form->createView()));
    }
    
    
    public function executePost()
    {
        $id = (int)$this->request->get('id', 0);

        $this->don = DonQuery::create()->filterById($id)->findOneOrCreate();

        $this->donateur = $this->don->getDonateur();

        $form = $this->getForm();

        $form->bind($this->request);
        
        if ($form->isValid()) {
            $form = $form->getData();

            $htis->don->setMontant($form['montant']);
        }
        
        return $this->getApp()->redirect('dons');
    }
    
    
    protected function getForm()
    {
        return $this->form
                        ->createBuilder('form')
                        ->add('id_donateur', 'hidden', array(
                            'data' => $this->donateur->getId(),
                            'required' => false,
                        ))
                        ->add('nom', 'text', array(
                            'data' => $this->donateur->getNom(),
                            'required' => true,
                            'label' => 'Nom',
                        ))
                        ->add('prenom', 'text', array(
                            'data' => $this->donateur->getPrenom(),
                            'required' => false,
                            'label' => 'Prénom',
                        ))
                        ->add('email', 'email', array(
                            'data' => $this->donateur->getEmail(),
                            'required' => false,
                            'label' => 'Adresse e-mail',
                        ))
                        ->add('rue', 'textarea', array(
                            'data' => $this->donateur->getRue(),
                            'required' => false,
                            'label' => 'Adresse',
                        ))
                        ->add('cp', 'text', array(
                            'data' => $this->donateur->getCp(),
                            'required' => false,
                            'label' => 'Code postal',
                        ))
                        ->add('ville', 'text', array(
                            'data' => $this->donateur->getVille(),
                            'required' => false,
                            'label' => 'Ville',
                        ))
                        ->add('pays', 'text', array(
                            'data' => $this->donateur->getPays(),
                            'required' => false,
                            'label' => 'Pays',
                        ))
                        ->add('commentaire', 'textarea', array(
                            'data' => $this->donateur->getCommentaire(),
                            'required' => false,
                            'label' => 'Commentaire',
                        ))
                        ->add('type_donateur', 'choice', array(
                            'data' => $this->donateur->getTypeDonateur(),
                            'required' => true,
                            'label' => 'Type de donateur',
                            'choices' => array('Personnel' => 'Personnel', 'Entreprise' => 'Entreprise'),
                        ))
                        ->add('montant', 'money', array(
                            'data' => $this->don->getMontant(),
                            'required' => true,
                            'label' => 'Montant du don',
                        ))
                        ->getForm();
    }
}