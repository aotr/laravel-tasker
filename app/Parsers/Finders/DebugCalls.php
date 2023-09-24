<?php

namespace Aotr\Tasker\Parsers\Finders;

use PhpParser\Node;

class DebugCalls
{
    /**
     * Search for debug calls in the given node.
     *
     * @param  Node  $node
     * @return bool
     */
    public function search(Node $node): bool
    {
        if (! $node instanceof Node\Expr\FuncCall) {
            return false;
        }
        
        if (! $node->name instanceof Node\Name) {
            return false;
        }

        $functionName = $node->name->toLowerString();

        if (! in_array($functionName, ['var_dump', 'var_export', 'dd', 'print_r'])) {
            return false;
        }

        if (
            in_array($functionName, ['var_export', 'print_r'])
            && count($node->args) === 2
            && $node->args[1]->value instanceof Node\Expr\ConstFetch
            && $node->args[1]->value->name->toLowerString() === 'true'
        ) {
            return false;
        }

        // TODO: filter special calls, e.g. `var_export($foo, true)`

        return true;
    }

    /**
     * Process the instances of debug calls and return the formatted output.
     *
     * @param  array  $instances
     * @return array
     */
    public function process(array $instances): array
    {
        $output = [];

        foreach ($instances as $instance) {
            $output[] = [
                'line' => [
                    'start' => $instance->getStartLine(),
                    'end' => $instance->getEndLine(),
                ],
                'offset' => [
                    'start' => $instance->getStartFilePos(),
                    'end' => $instance->getEndFilePos(),
                ],
                'function' => $instance->name->toLowerString(),
            ];
        }

        return $output;
    }
}
