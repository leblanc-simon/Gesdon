<?php

namespace Gesdon\Database;

use Gesdon\Database\om\BaseTaskManager;


/**
 * Skeleton subclass for representing a row from the 'task_manager' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.Gesdon.Database
 */
class TaskManager extends BaseTaskManager
{
    public function getParamToString()
    {
        $params = json_decode($this->getParam());
        if (false === $params) {
            return null;
        }

        $string = '';
        foreach ($params as $key => $value) {
            $string .= (empty($string) ? '' : ', ').$key.' = '.$value;
        }

        return $string;
    }
}
