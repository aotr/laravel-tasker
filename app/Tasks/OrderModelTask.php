<?php

namespace Aotr\Tasker\Tasks;

use Aotr\Tasker\Traits\FindsFiles;

class OrderModelTask
{
    use FindsFiles;

    protected const METHOD_ORDER = [
        'constructor',
        'booting',
        'boot',
        'booted',
        'relationship',
        'attribute',
        'scope',
        'static',
        'custom',
    ];

    /**
     * Perform the task of ordering model definitions.
     *
     * @return int
     */
    public function perform(): int
    {
        $files = $this->findFiles();
        if (empty($files)) {
            return 0;
        }

        $finder = new \App\Parsers\NikicParser(new \App\Parsers\Finders\ClassDefinition());

        foreach ($files as $file) {
            $lines = file($file);

            $instances = $finder->parse(implode($lines));
            if (empty($instances)) {
                continue;
            }

            $definitions = '';

            foreach ($this->orderByName($instances['constants']) as $constant) {
                $definitions .= $this->extractDefinition($constant, $lines) . PHP_EOL;
            }

            foreach ($this->orderByName($instances['properties']) as $property) {
                $definitions .= $this->extractDefinition($property, $lines) . PHP_EOL;
            }

            $methods = $this->addMethodType($instances['methods'], $lines);
            foreach ($this->orderByType($methods) as $method) {
                $definitions .= $this->extractDefinition($method, $lines) . PHP_EOL;
            }

            $contents = implode(array_slice($lines, 0, $this->minLine($instances) - 1));
            $contents .= $definitions;
            $contents .= implode(array_slice($lines, $this->maxLine($instances)));

            file_put_contents($file, $contents);
        }

        return 0;
    }

    /**
     * Add method types to the methods array.
     *
     * @param  array  $methods
     * @param  array  $lines
     * @return array
     */
    private function addMethodType(array $methods, array $lines): array
    {
        return array_map(
            function ($method) use ($lines) {
                $method['type'] = $this->getMethodType($method, $this->extractDefinition($method, $lines));

                return $method;
            },
            $methods
        );
    }

    /**
     * Extract the method definition from the lines of code.
     *
     * @param  array  $definition
     * @param  array  $lines
     * @return string
     */
    private function extractDefinition(array $definition, array $lines): string
    {
        $start = $definition['comment'] ? $definition['comment']['line']['start'] : $definition['line']['start'];

        return implode(array_slice($lines, $start - 1, $definition['line']['end'] - $start + 1));
    }

    /**
     * Determine the type of a method.
     *
     * @param  array  $method
     * @param  string  $block
     * @return string
     */
    private function getMethodType(array $method, string $block): string
    {
        if ($method['name'] === '__construct') {
            return 'constructor';
        }

        if ($method['visibility'] === 'public' && $method['static'] && in_array($method['name'], ['booting', 'boot', 'booted'])) {
            return $method['name'];
        }

        // Relationship
        if ($method['visibility'] === 'public' && preg_match('/\s+return\s+\$this->(hasOne|belongsTo|hasMany|belongsToMany|hasManyThrough|morphTo|morphMany|morphToMany|morphedByMany)\(/', $block)) {
            return 'relationship';
        }

        if ((str_starts_with($method['name'], 'get') || str_starts_with($method['name'], 'set')) && str_ends_with($method['name'], 'Attribute')) {
            return 'attribute';
        }

        if (str_starts_with($method['name'], 'scope')) {
            return 'scope';
        }

        if ($method['static']) {
            return 'scope';
        }

        return 'custom';
    }

    /**
     * Get the minimum starting line among the definitions.
     *
     * @param  array  $instances
     * @return int
     */
    private function minLine(array $instances): int
    {
        return min(
            $this->minStartLine($instances['constants']),
            $this->minStartLine($instances['properties']),
            $this->minStartLine($instances['methods'])
        );
    }

    /**
     * Get the maximum starting line among the definitions.
     *
     * @param  array  $instances
     * @return int
     */
    private function maxLine(array $instances): int
    {
        return max(
            $this->maxStartLine($instances['constants']),
            $this->maxStartLine($instances['properties']),
            $this->maxStartLine($instances['methods'])
        );
    }

    /**
     * Get the minimum starting line among the definitions.
     *
     * @param  array  $definitions
     * @return int
     */
    private function minStartLine(array $definitions): int
    {
        if (empty($definitions)) {
            return PHP_INT_MAX;
        }

        return min(array_map(
            fn ($definition) => $definition['comment'] ? $definition['comment']['line']['start'] : $definition['line']['start'],
            $definitions
        ));
    }

    /**
     * Get the maximum starting line among the definitions.
     *
     * @param  array  $definitions
     * @return int
     */
    private function maxStartLine(array $definitions): int
    {
        if (empty($definitions)) {
            return 0;
        }

        return max(array_map(
            fn ($definition) => $definition['line']['end'],
            $definitions
        ));
    }

    /**
     * Order the items by their names.
     *
     * @param  array  $items
     * @return array
     */
    private function orderByName(array $items): array
    {
        uksort($items, fn ($a, $b) => strnatcmp($a, $b));

        return $items;
    }

    /**
     * Order the methods by their types and names.
     *
     * @param  array  $methods
     * @return array
     */
    private function orderByType(array $methods): array
    {
        usort($methods, function ($a, $b) {
            if ($a['type'] === $b['type']) {
                return strnatcmp($a['name'], $b['name']);
            }

            return array_search($a['type'], self::METHOD_ORDER) - array_search($b['type'], self::METHOD_ORDER);
        });

        return $methods;
    }

    /**
     * Get the subpath for finding files.
     *
     * @return string
     */
    protected function subPath(): string
    {
        return 'app/Models';
    }
}
