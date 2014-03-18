<?php

namespace Gesdon\App;

use Gesdon\Core\Exception;
use Gesdon\Database\TaskManagerPeer;

class TaskRecu extends Main
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

            TaskManagerPeer::add('send:recus', $params);

            // Add success message
            $this->session->getFlashBag()->add('notice', 'L\'envoi des reçus fiscaux a été programmé.');
        } catch (\Exception $e) {
            // Add error message
            $this->session->getFlashBag()->add('error', 'L\'envoi des reçus fiscaux n\'a pas été programmé : '.$e->getMessage());
            return $this->getApp()->redirect($this->url->generate('task_recus'));
        }

        return $this->getApp()->redirect($this->url->generate('task'));
    }
}