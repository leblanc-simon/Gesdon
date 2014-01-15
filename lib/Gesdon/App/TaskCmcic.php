<?php

namespace Gesdon\App;

use Gesdon\Core\Exception;
use Gesdon\Database\TaskManagerPeer;

class TaskCmcic extends Main
{
    public function executeGet()
    {
        $begin = new \DateTime();
        $begin->setDate($begin->format('Y') - 1, 1, 1);
        $begin->setTime(0, 0, 0);

        $end = new \DateTime();
        $end->setDate($end->format('Y') - 1, 12, 31);
        $end->setTime(0, 0, 0);

        return $this->render(array('begin' => $begin, 'end' => $end));
    }

    public function executePost()
    {
        try {
            $begin = trim((string)$this->request->get('begin'));
            $end = trim((string)$this->request->get('end'));

            $params = array('debut' => $begin, 'fin' => $end);

            foreach ($params as $key => $value) {
                if (preg_match('#[0-9]{4}-[0-9]{2}-[0-9]{2}#', $value) === 0) {
                    if (preg_match('#(?<day>[0-9]{2})/(?<month>[0-9]{2})/(?<year>[0-9]{4})#', $value, $matches) === 1) {
                        $params[$key] = $matches['year'].'-'.$matches['month'].'-'.$matches['day'];
                    } else {
                        throw new Exception($key.' must be a date');
                    }
                }
            }

            $group_name = sha1(uniqid('task', true).mt_rand(0, 999999));

            // Add import CMCIC website data in CSV file
            TaskManagerPeer::add('migrate:build-recurrent', $params, $group_name, 0);

            // Add CMCIC CSV data in the database
            TaskManagerPeer::add('migrate:data', array('--cmcic-recurrent' => true), $group_name, 1);

            // Add success message
            $this->session->getFlashBag()->add('notice', 'L\'importation des données du Crédit Mutuel a été programmée.');
        } catch (\Exception $e) {
            // Add error message
            $this->session->getFlashBag()->add('error', 'L\'importation des données du Crédit Mutuel n\'a pas été programmée : '.$e->getMessage());
        }

        return $this->getApp()->redirect($this->url->generate('task'));
    }
}