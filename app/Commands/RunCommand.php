<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class RunCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'run {task* :The name of task}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Run one or more tasks';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
    }
    private function taskRe(Type $var = null)
    {
        # code...
    }
}
