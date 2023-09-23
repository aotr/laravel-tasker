<?php

namespace Aotr\Tasker\Parsers\Finders;

use PhpParser\Node;

class ClassDefinition
{
    /**
     * Search for class definitions in the given node.
     *
     * @param  Node  $node
     * @return bool
     */
    public function search(Node $node): bool
    {
        return $node instanceof Node\Stmt\Class_;
    }

    /**
     * Process the instances of class definitions and return the formatted output.
     *
     * @param  array  $instances
     * @return array
     */
    public function process(array $instances): array
    {
        $class = $instances[0];

        return [
            'line' => [
                'start' => $class->getStartLine(),
                'end' => $class->getEndLine(),
            ],
            'offset' => [
                'start' => $class->getStartFilePos(),
                'end' => $class->getEndFilePos(),
            ],
            'constants' => $this->getConstants($class),
            'properties' => $this->getProperties($class),
            'methods' => $this->getMethods($class),
        ];
    }

    /**
     * Get the comments associated with the given node.
     *
     * @param  Node  $node
     * @return array|null
     */
    private function getComments(Node $node): ?array
    {
        $comments = $node->getComments();
        if (empty($comments)) {
            return null;
        }

        $last = array_key_last($comments);

        return [
            'line' => [
                'start' => $comments[0]->getStartLine(),
                'end' => $comments[$last]->getEndLine(),
            ],
            'offset' => [
                'start' => $comments[0]->getStartFilePos(),
                'end' => $comments[$last]->getEndFilePos(),
            ],
        ];
    }

    /**
     * Get the constants defined in the given class.
     *
     * @param  Node\Stmt\Class_  $class
     * @return array
     */
    private function getConstants(Node\Stmt\Class_ $class): array
    {
        $constants = [];
        foreach ($class->getConstants() as $constant) {
            $name = $constant->consts[0]->name->toString();
            $constants[$name] = [
                'line' => [
                    'start' => $constant->getStartLine(),
                    'end' => $constant->getEndLine(),
                ],
                'offset' => [
                    'start' => $constant->getStartFilePos(),
                    'end' => $constant->getEndFilePos(),
                ],
                'comment' => $this->getComments($constant),
                'name' => $name,
                'visibility' => $this->getVisibility($constant),
            ];
        }

        return $constants;
    }

    /**
     * Get the methods defined in the given class.
     *
     * @param  Node\Stmt\Class_  $class
     * @return array
     */
    private function getMethods(Node\Stmt\Class_ $class): array
    {
        $methods = [];
        foreach ($class->getMethods() as $method) {
            $name = $method->name->toString();
            $methods[$name] = [
                'line' => [
                    'start' => $method->getStartLine(),
                    'end' => $method->getEndLine(),
                ],
                'offset' => [
                    'start' => $method->getStartFilePos(),
                    'end' => $method->getEndFilePos(),
                ],
                'comment' => $this->getComments($method),
                'name' => $name,
                'visibility' => $this->getVisibility($method),
                'static' => $method->isStatic(),
            ];
        }

        return $methods;
    }

    /**
     * Get the properties defined in the given class.
     *
     * @param  Node\Stmt\Class_  $class
     * @return array
     */
    private function getProperties(Node\Stmt\Class_ $class): array
    {
        $properties = [];
        foreach ($class->getProperties() as $property) {
            $name = $property->props[0]->name->toString();
            $properties[$name] = [
                'line' => [
                    'start' => $property->getStartLine(),
                    'end' => $property->getEndLine(),
                ],
                'offset' => [
                    'start' => $property->getStartFilePos(),
                    'end' => $property->getEndFilePos(),
                ],
                'comment' => $this->getComments($property),
                'name' => $name,
                'visibility' => $this->getVisibility($property),
                'static' => $property->isStatic(),
            ];
        }

        return $properties;
    }

    /**
     * Get the visibility of the given node.
     *
     * @param  Node  $node
     * @return string
     */
    private function getVisibility(Node $node): string
    {
        if ($node->isPrivate()) {
            return 'private';
        } elseif ($node->isProtected()) {
            return 'protected';
        }

        return 'public';
    }
}
