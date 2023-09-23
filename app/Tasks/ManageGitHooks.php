<?php

namespace App\Tasks;

class ManageGitHooks 
{
    public function perform(): int
    {
        $hooks = ['pre-commit', 'prepare-commit-msg'];
        foreach ($hooks as $hook) {
            $choice = $this->choice("What would you like to do with the {$hook} git hook?", ['Enable', 'Disable', 'Skip'], 2);

            switch ($choice) {
                case 'Enable':
                    if (!$this->enableHook($hook)) {
                        $this->warn("Failed to enable {$hook} hook.");
                        return 1;  // Indicates an error
                    }
                    break;
                case 'Disable':
                    if (!$this->disableHook($hook)) {
                        $this->warn("Failed to disable {$hook} hook.");
                        return 1;  // Indicates an error
                    }
                    break;
                case 'Skip':
                default:
                    $this->info("Skipped managing {$hook} hook.");
                    break;
            }
        }

        return 0;  // Indicates success
    }

    private function enableHook($hookName): bool
    {
        $hookPath = getcwd() . "/.git/hooks/{$hookName}";
        $disabledHooks = glob(getcwd() . "/.git/hooks/{$hookName}_disabled_*");
        if (empty($disabledHooks)) {
            $this->info("No disabled {$hookName} hook found.");
            return true;
        }
        $mostRecentDisabledHook = end($disabledHooks);
        return rename($mostRecentDisabledHook, $hookPath);
    }

    private function disableHook($hookName): bool
    {
        $hookPath = getcwd() . "/.git/hooks/{$hookName}";
        if (!file_exists($hookPath)) {
            $this->info("{$hookName} hook is already disabled.");
            return true;
        }
        $disabledHookPath = "{$hookPath}_disabled_" . time();
        return rename($hookPath, $disabledHookPath);
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
