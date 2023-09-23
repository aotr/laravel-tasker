<?php

namespace Aotr\Tasker\Tasks;

class InstallPreCommitHook 
{
    public function perform(): int
    {
        // Install the pre-commit hook
        if ($this->installHook('pre-commit') !== 0) {
            return 1; // Indicates an error
        }

        // Install the prepare-commit-msg hook
        if ($this->installHook('prepare-commit-msg') !== 0) {
            return 1; // Indicates an error
        }

        return 0; // Indicates success
    }

    private function installHook($hookName): int
    {
        $hookPath = getcwd() . "/.git/hooks/{$hookName}";
        $stubPath = getcwd() . "/stubs/{$hookName}";

        if (file_exists($hookPath)) {
            $choice = $this->choice("The {$hookName} hook already exists. What would you like to do?", ['Overwrite', 'Overwrite with Backup', 'Cancel'], 2);

            switch ($choice) {
                case 'Overwrite':
                    break;
                case 'Overwrite with Backup':
                    $backupPath = $hookPath . '_' . time();
                    rename($hookPath, $backupPath);
                    $this->info("Backup created at: {$backupPath}");
                    break;
                case 'Cancel':
                default:
                    $this->info("Installation of {$hookName} hook canceled.");
                    return 1;  // Indicates an error or intentional cancel
            }
        }

        copy($stubPath, $hookPath);

        // Apply execute permission
        if (chmod($hookPath, 0755)) {
            $this->info("{$hookName} hook installed successfully!");
            return 0;  // Indicates success
        } else {
            $this->warn("Failed to apply execute permission to {$hookName} hook. Please do it manually:");
            $this->line('For Ubuntu/Mac: chmod +x ' . $hookPath);
            $this->line('For Windows: Check properties and provide execute permission.');
            return 1;  // Indicates an error
        }
    }

    private function choice($question, $choices, $default)
    {
        echo $question . PHP_EOL;
        foreach ($choices as $key => $value) {
            echo "[$key] $value" . PHP_EOL;
        }
        $selection = readline("Choice [$default]: ");
        return $choices[$selection] ?? $choices[$default];
    }

    private function info($message)
    {
        echo "[INFO] $message" . PHP_EOL;
    }

    private function warn($message)
    {
        echo "[WARNING] $message" . PHP_EOL;
    }

    private function line($message)
    {
        echo $message . PHP_EOL;
    }
}
