<?php

namespace Gesdon\App;

use Gesdon\Core\Config;
use Gesdon\Database\TaskManagerPeer;
use Gesdon\Database\TaskManagerQuery;
use Gesdon\Task\Manager;

class TaskLaunch extends Main
{
    public function executeGet()
    {
        $tasks = TaskManagerQuery::create()
            ->filterByExecuted(false)
            ->filterByDateToExecute(new \DateTime(), \Criteria::LESS_EQUAL)
            ->orderByGroup()
            ->orderByPosition()
            ->orderByDateToExecute()
            ->find();

        $is_running = Manager::isRunning();

        return $this->render(array('tasks' => $tasks, 'is_running' => $is_running));
    }

    public function executeDelete()
    {
        $id = (int)$this->request->get('id', 0);
        $task_manager = TaskManagerPeer::retrieveByPK($id);

        if (null === $task_manager) {
            $this->session->getFlashBag()->add('error', 'Impossible de trouver la tâche à supprimer');
            return $this->getApp()->redirect($this->url->generate('task_launch'));
        }

        $group = $task_manager->getGroup();
        if (empty($group) === true) {
            $task_manager->delete();
            $this->session->getFlashBag()->add('notice', 'La tâche a bien été supprimée');
        } else {
            $criteria = new \Criteria();
            $criteria->add(TaskManagerPeer::GROUP, $group);
            TaskManagerPeer::doDelete($criteria);
            $this->session->getFlashBag()->add('notice', 'Les tâches ont bien été supprimées');
        }

        return $this->getApp()->redirect($this->url->generate('task_launch'));
    }


    public function executePost()
    {
        $cmd = Config::get('base_dir').'/task/run.php manager:run > /dev/null 2>&1 &';

        exec($cmd, $output, $return);

        return $this->getApp()->redirect($this->url->generate('task'));
    }
}