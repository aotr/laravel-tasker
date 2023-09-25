<?php

namespace Aotr\Tasker\Commands;

use Aotr\Tasker\Support\TaskManifest;
use LaravelZero\Framework\Commands\Command;

class DiscoverCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'discover';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Load any additional automated tasks within the project';

    /**
     * Execute the console command.
     *
     * @param  \App\Support\TaskManifest  $manifest
     * @return void
     */
    public function handle(TaskManifest $manifest)
    {
        // Display information about the task discovery process
        $this->info('Discovering tasks');

        // Build the task manifest
        $manifest->build();
        ;
        // Iterate through the list of tasks and execute each task
        collect($manifest->list())
            ->keys()
            ->each(fn ($task) => $this->task($task));

        // Add a new line after displaying the tasks
        $this->newLine();
    }
}
