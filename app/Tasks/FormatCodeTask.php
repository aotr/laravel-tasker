<?php

namespace Aotr\Tasker\Tasks;

class FormatCodeTask
{
    /**
     * Perform the code formatting task.
     *
     * @return int
     */
    public function perform(): int
    {
        exec('vendor/bin/pint', $output, $exitCode);

        return $exitCode;
    }
}
