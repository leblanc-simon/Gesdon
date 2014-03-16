<?php

namespace Gesdon\Task;

use Gesdon\Core\Exception;
use Gesdon\Database\TaskManagerQuery;
use Gesdon\Core\Config;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class Manager extends BaseTask
{
    protected function configure()
    {
        $this
            ->setName('manager:run')
            ->setDescription('Execute toutes les tâches en attente dans le manager');
    }


    /**
     * Execution de la tâche
     *
     * @param   InputInterface   $input  les entrées de la console
     * @param   OutputInterface $output les sortie de la console
     * @access  protected
     * @return  void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (static::isRunning() === true) {
            throw new Exception('Impossible to run manager twice!');
        }

        file_put_contents(static::getPidFile(), getmypid());

        try {
            $this->setOutput($output);

            $this->runTasks();

            unlink(static::removePidFile());
        } catch (\Exception $e) {
            unlink(static::removePidFile());
            throw $e;
        }
    }


    private function runTasks()
    {
        $tasks = TaskManagerQuery::create()
                    ->filterByExecuted(false)
                    ->filterByDateToExecute(new \DateTime(), \Criteria::LESS_EQUAL)
                    ->find();

        $this->log(count($tasks).' to execute');

        foreach ($tasks as $task) {
            $this->log('Launch '.$task->getTaskName());

            try {
                $command = $this->getApplication()->find($task->getTaskName());
                if (null !== $command) {
                    $input = new ArrayInput(array_merge(
                        array(
                            'command' => $task->getTaskName()
                        ),
                        json_decode($task->getParam(), true)
                    ));
                    if ($command->run($input, $this->getOutputStream()) === 0) {
                        $task->setExecuted(true);
                        $task->setExecutedAt(new \DateTime());
                        $task->save();
                    }
                }
            } catch (\Exception $e) {
                $this->log($e->getMessage(), 'error');
            }
        }
    }


    /**
     * Return the output to use for the task
     *
     * @return NullOutput|StreamOutput
     */
    private function getOutputStream()
    {
        static $output = null;

        if (null === $output) {
            $log_handle = fopen(Config::get('log_dir').'/task-manager-'.date('Y-m-d').'.log', 'ab');
            if (false !== $log_handle) {
                $output = new StreamOutput($log_handle);
            } else {
                $output = new NullOutput();
            }
        }

        return $output;
    }


    static public function isRunning()
    {
        if (file_exists(static::getPidFile()) === false) {
            return false;
        }

        $pid = file_get_contents(static::getPidFile());
        $pids = explode("\n", trim(`ps -e | awk '{print $1}'`));
        if(in_array($pid, $pids) === false) {
            return false;
        }

        return true;
    }


    static private function getPidFile()
    {
        return Config::get('data_dir').'/task.pid';
    }


    static private function removePidFile()
    {
        if (file_exists(static::getPidFile()) === true) {
            unlink(static::getPidFile());
        }
    }
}