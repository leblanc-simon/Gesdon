<?php

namespace Gesdon\Task;

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
     * @param   \Symfony\Component\Console\Input\InputInterface   $input  les entrées de la console
     * @param   \Symfony\Component\Console\Output\OutputInterface $output les sortie de la console
     * @access  protected
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setOutput($output);

        $this->runTasks();
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
                    $input = new ArrayInput(json_decode($task->getParam()));
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
}