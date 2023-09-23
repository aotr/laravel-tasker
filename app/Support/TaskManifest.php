<?php

namespace Aotr\Tasker\Support;

/**
 * The TaskManifest class handles the management of tasks in the project.
 */
class TaskManifest
{
    /**
     * The task manifest array.
     *
     * @var array
     */
    private array $manifest = [];

    /**
     * The path to the task manifest file.
     *
     * @var string
     */
    private string $manifestPath;

    /**
     * The vendor path.
     *
     * @var string
     */
    private string $vendorPath;

    /**
     * Create a new TaskManifest instance.
     *
     * @param string $vendorPath The path to the vendor directory.
     */
    public function __construct(string $vendorPath)
    {
        $this->manifestPath = $vendorPath . '/tasker-tasks.php';
        $this->vendorPath = $vendorPath;
    }

    /**
     * Get the list of tasks from the manifest.
     *
     * @return array The list of tasks.
     */
    public function list(): array
    {
        if (!empty($this->manifest)) {
            return $this->manifest;
        }

        if (!is_file($this->manifestPath)) {
            $this->build();
        }

        return $this->manifest = is_file($this->manifestPath)
            ? require $this->manifestPath
            : [];
    }

    /**
     * Build the task manifest by scanning installed packages.
     *
     * @return void
     */
    public function build(): void
    {
        $packages = [];

        $installedJsonPath = $this->vendorPath . '/composer/installed.json';

        if (file_exists($installedJsonPath)) {
            $installed = json_decode(file_get_contents($installedJsonPath), true);
            $packages = $installed['packages'] ?? $installed;
        }

        $this->write(
            collect($packages)
                ->mapWithKeys(function ($package) {
                    return $package['extra']['tasker']['tasks'] ?? [];
                })
                ->filter()
                ->merge($this->defaultTasks())
                ->all()
        );
    }

    /**
     * Get the default tasks.
     *
     * @return array The default tasks.
     */
    private function defaultTasks(): array
    {
        return [
            'check-lint' => \App\Tasks\CheckLint::class,
            'debug-calls' => \App\Tasks\DebugCalls::class,
            'format-code' => \App\Tasks\FormatCode::class,
            'order-model' => \App\Tasks\OrderModel::class,
            'declare-strict' => \App\Tasks\DeclareStrictTypes::class,
            'remove-docblocks' => \App\Tasks\RemoveDocBlocks::class,
        ];
    }

    /**
     * Write the task manifest array to the manifest file.
     *
     * @param array $manifest The task manifest array.
     * @return void
     * @throws \Exception If the directory is not writable.
     */
    protected function write(array $manifest): void
    {
        $dirname = dirname($this->manifestPath);

        if (!is_writable($dirname)) {
            throw new \Exception("The {$dirname} directory must be present and writable.");
        }

        file_put_contents(
            $this->manifestPath,
            '<?php return ' . var_export($manifest, true) . ';'
        );
    }
}
