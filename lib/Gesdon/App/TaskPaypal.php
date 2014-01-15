<?php

namespace Gesdon\App;

use Gesdon\Core\Exception;
use Gesdon\Database\TaskManagerPeer;
use Gesdon\Core\Config;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class TaskPaypal extends Main
{
    public function executeGet()
    {
        return $this->render(array('form' => $this->getForm()->createView()));
    }

    public function executePost()
    {
        try {
            $form = $this->getForm();

            $form->bind($this->request);
            if ($form->isValid() === false) {
                $this->session->getFlashBag()->add('error', 'L\'importation des données Paypal n\'a pas été programmée');
                $this->session->getFlashBag()->add('error', $form->getErrorsAsString());
                return $this->getApp()->redirect($this->url->generate('task_paypal'));
            }

            $file = $form->get('file');
            if ($file === null) {
                throw new \Exception('No file to upload');
            }

            $file = $file->getNormData();
            if (!($file instanceof UploadedFile)) {
                throw new \Exception('No file to upload');
            }
            $file->move(Config::get('data_dir'), 'paypal.csv');

            // Add CMCIC CSV data in the database
            TaskManagerPeer::add('migrate:data', array('--paypal' => true));

            // Add success message
            $this->session->getFlashBag()->add('notice', 'L\'importation des données Paypal a été programmée.');
        } catch (\Exception $e) {
            // Add error message
            $this->session->getFlashBag()->add('error', 'L\'importation des données Paypal n\'a pas été programmée : '.$e->getMessage());
            return $this->getApp()->redirect($this->url->generate('task_paypal'));
        }

        return $this->getApp()->redirect($this->url->generate('task'));
    }


    private function getForm()
    {
        return $this->form->createBuilder('form', null)
            ->add('file', 'file', array(
                'required' => true,
                'label' => 'Fichier Paypal CSV',
                'constraints' => new Assert\File(array(
                    'mimeTypes' => array(
                        'text/*',
                        'application/octet-stream',
                    )
                )),
            ))->getForm();
    }
}