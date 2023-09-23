<?php

namespace Aotr\Tasker\Tasks;

use Aotr\Tasker\Traits\FindsFiles;

class CheckLint
{
    use FindsFiles;

    /**
     * Perform the lint check on files.
     *
     * @return int
     */
    public function perform(): int
    {
        $files = $this->findFiles();
        if (empty($files)) {
            return 0;
        }

        $failure = false;
        foreach ($files as $file) {
            $output = [];
            $exitCode = 0;
            exec('php -l ' . $file . ' 2>&1', $output, $exitCode);

            if ($exitCode !== 0) {
                [$line, $error] = $this->parseError($output);
                $this->displayError($file, $line, $error);
                $failure = true;
            }
        }

        return $failure ? 1 : 0;
    }

    /**
     * Parse the error from the output lines.
     *
     * @param array $lines
     * @return array [$line, $error]
     */
    private function parseError(array $lines): array
    {
        preg_match('/PHP (?:Fatal|Parse) error:\s+(?:syntax error, )?(.+?)\s+in\s+.+?\.php\s+on\s+line\s+(\d+)/', $lines[0], $matches);

        return [$matches[2], $matches[1]];
    }

    /**
     * Display the error information.
     *
     * @param string $path
     * @param int $line
     * @param string $error
     * @return void
     */
    private function displayError(string $path, int $line, string $error): void
    {
        echo $path . PHP_EOL;
        echo '  - Line ' . $line . ': ' . $error . PHP_EOL . PHP_EOL;
    }
}
