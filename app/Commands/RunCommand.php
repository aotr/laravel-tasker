<?php

namespace Aotr\Tasker\Commands;

use Aotr\Tasker\Support\TaskManifest;
use Aotr\Tasker\Traits\FindsFiles;
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
    $tasks = [
        'check-lint' => \Aotr\Tasker\Tasks\CheckLint::class,
        'debug-calls' => \Aotr\Tasker\Tasks\DebugCallsTask::class,
        'format-code' => \Aotr\Tasker\Tasks\FormatCodeTask::class,
        'order-model' => \Aotr\Tasker\Tasks\OrderModelTask::class,
        'declare-strict' => \Aotr\Tasker\Tasks\DeclareStrictTypesTask::class,
        'remove-docblocks' => \Aotr\Tasker\Tasks\RemoveDocBlocks::class,
        'hook-install' => \Aotr\Tasker\Tasks\InstallPreCommitHook::class,
        'hook-manage' => \Aotr\Tasker\Tasks\ManageGitHooks::class,
    ];

    if (!isset($tasks[$task])) {
        $this->displayAvailableTasks($tasks);
        throw new InvalidArgumentException('Invalid task name: ' . $task);
    }

    return $tasks[$task];
}

private function displayAvailableTasks(array $tasks): void
{
    echo "Available tasks:" . PHP_EOL;
    foreach (array_keys($tasks) as $taskName) {
        echo "- " . $taskName . PHP_EOL;
    }
}

}
