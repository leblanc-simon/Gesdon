<?php

namespace Gesdon\Database;

use Gesdon\Database\om\BaseTaskManagerPeer;


/**
 * Skeleton subclass for performing query and update operations on the 'task_manager' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.Gesdon.Database
 */
class TaskManagerPeer extends BaseTaskManagerPeer
{
    /**
     * Add a task in the task manager
     *
     * @param   string  $task_name  Name of the task to add
     * @param   array   $params     The parameter to use for the task
     * @param   string  $group      The group name to group task
     * @param   int     $position   The position of the task in the group
     * @return TaskManager The task manager created
     */
    static public function add($task_name, array $params = array(), $group = null, $position = null)
    {
        $manager = new TaskManager();
        $manager->setTaskName($task_name);
        $manager->setParam(json_encode($params));
        $manager->setDateToExecute(new \DateTime());
        $manager->setExecuted(false);
        $manager->setExecutedAt(null);
        $manager->setGroup($group);
        $manager->setPosition($position);
        $manager->save();

        return $manager;
    }
}
