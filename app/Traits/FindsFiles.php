<?php

namespace App\Traits;

use App\Facades\Configuration;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
/**
 * Trait FindsFiles
 *
 * This trait provides functionality for finding PHP files within a specified subpath, either by scanning the directory
 * or by using Git to find only dirty files. It is designed to be used in classes or traits that need to perform operations
 * on a set of PHP files.
 *
 * Usage:
 * - Include the trait in your class or trait.
 * - Override the `subPath` method to specify the subpath where the PHP files are located.
 * - You can use the `findFiles` method to retrieve an array of PHP file paths.
 * - If you want to find only dirty files using Git, you can set the `$dirty` property to `true` and call the `findFiles` method.
 * - You can also manually set the `$files` property with an array of file paths if you want to bypass the file search.
 *
 * @package App\Traits
 */

trait FindsFiles
{
    protected array $files = [];
    protected bool $dirty = false;

    protected function findFiles(): array
    {
        if (!empty($this->files)) {
            return $this->files;
        }

        if ($this->dirty) {
            return $this->findDirtyFiles();
        }

        $finder = new Finder();
        $finder->files()
            ->in(rtrim(getcwd() . DIRECTORY_SEPARATOR . $this->subPath(), DIRECTORY_SEPARATOR))
            ->exclude('vendor')
            ->notPath(Configuration::get('ignore', []))
            ->name('*.php');

        return array_map(fn ($file) => $file->getRealPath(), iterator_to_array($finder, false));
    }

    protected function findDirtyFiles(): array
    {
        $process = tap(new Process(['git', 'status', '--short', '--', '*.php']))->run();

        if (!$process->isSuccessful()) {
            abort(1, 'The [--dirty] option is only available when using Git.');
        }

        return collect(preg_split('/\R+/', $process->getOutput(), flags: PREG_SPLIT_NO_EMPTY))
            ->mapWithKeys(fn ($file) => [substr($file, 3) => trim(substr($file, 0, 3))])
            ->reject(fn ($status) => $status === 'D')
            ->map(fn ($status, $file) => $status === 'R' ? Str::after($file, ' -> ') : $file)
            ->values()
            ->all();
    }

    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    public function setDirty(bool $dirty): void
    {
        $this->dirty = $dirty;
    }

    protected function subPath(): string
    {
        return '';
    }
}
