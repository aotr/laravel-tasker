<?php

namespace App\Commands;

use App\Support\TaskManifest;
use App\Traits\FindsFiles;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;

/**
 * The RunCommand class is responsible for running one or more automated tasks.
 */
class RunCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'run {task* : The name of the automated task} {--dirty} {--path=* : The paths to scan}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Run one or more automated tasks';

    /**
     * Handle the command execution.
     *
     * @return int The exit code (0 for success, non-zero for failure).
     */
    public function handle(): int
    {
        foreach ($this->argument('task') as $task) {
            $result = $this->createTask($this->taskRegistry($task))->perform();
            if ($result !== 0) {
                $this->error('Failed to run task: ' . $task);
                return $result;
            }
        }

        return 0;
    }

    /**
     * Create an instance of the specified task and configure it.
     *
     * @param string $name The fully qualified name of the task.
     * @return object The instantiated task object.
     */
    private function createTask(string $name): object
    {
        $task = new $name;

        if (in_array(FindsFiles::class, class_uses_recursive($task))) {
            if ($this->option('path')) {
                $task->setFiles($this->option('path'));
            }

            if ($this->option('dirty')) {
                $task->setDirty(true);
            }
        }

        return $task;
    }

    /**
     * Get the class name of the specified task from the task registry.
     *
     * @param string $task The name of the task.
     * @return string The fully qualified class name of the task.
     * @throws InvalidArgumentException if the task is not registered.
     */
    private function taskRegistry(string $task): string
    {
        $tasks = resolve(TaskManifest::class)->list();

        if (!isset($tasks[$task])) {
            throw new InvalidArgumentException('Task not registered: ' . $task);
        }

        return $tasks[$task];
    }
}
