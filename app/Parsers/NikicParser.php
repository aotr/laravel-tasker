<?php

namespace App\Parsers;

use PhpParser\Lexer\Emulative;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

class NikicParser
{
    /**
     * The finder instance.
     *
     * @var mixed
     */
    private $finder;

    /**
     * Create a new NikicParser instance.
     *
     * @param  mixed  $finder
     * @return void
     */
    public function __construct($finder)
    {
        $this->finder = $finder;
    }

    /**
     * Parse the given code and extract the desired information using the configured finder.
     *
     * @param  string  $code
     * @return array
     */
    public function parse(string $code): array
    {
        $lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine',
                'endLine',
                'startFilePos',
                'endFilePos',
            ],
        ]);
        $parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7, $lexer);
        $ast = $parser->parse($code);

        $nameResolver = new NameResolver(null, ['replaceNodes' => false]);
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($nameResolver);
        $ast = $nodeTraverser->traverse($ast);

        $nodeFinder = new NodeFinder();
        $instances = $nodeFinder->find($ast, [$this->finder, 'search']);

        return $this->finder->process($instances);
    }
}
