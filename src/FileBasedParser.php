<?php declare(strict_types=1);
/**
 * WordPress Core Bootstrap Documentation Parser.
 *
 * Parse the WordPress code to generate documentation for the Core Bootstrap project.
 *
 * @package   WPCoreBootstrap\DocumentationParser
 * @author    Alain Schlesser <alain.schlesser@gmail.com>
 * @license   MIT
 * @link      https://www.alainschlesser.com
 * @copyright 2017 Alain Schlesser, WordPress Core Bootstrap
 */

namespace WPCoreBootstrap\DocumentationParser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use WPCoreBootstrap\DocumentationParser\Visitor\NodeConnector;

/**
 * Class FileBasedParser.
 *
 * @since  0.1.0
 *
 * @author Alain Schlesser <alain.schlesser@gmail.com>
 */
final class FileBasedParser implements Parser
{
    /**
     * Root folder from which to load relative file names.
     *
     * @since 0.1.0
     *
     * @var string
     */
    private $root;

    /**
     * Instantiate a FileBasedParser object.
     *
     * @since 0.1.0
     *
     * @param string $root Root folder from which to load relative file names.
     */
    public function __construct(string $root)
    {
        $this->root = $this->normalizeRoot($root);
    }

    /**
     * Parse the code and return an array of AST trees.
     *
     * @since 0.1.0
     *
     * @param string $file Optional. Name (and relative path) of the file to parse.
     *
     * @return Node[] Array of abstract syntax tree nodes.
     */
    public function parse(string $file = null): array
    {
        if (null === $file) {
            return [];
        }

        $source = file_get_contents($this->root . $file);
        $parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP5);
        $ast    = $parser->parse($source);
        $ast    = $this->addConnections($ast);
        return $ast;
    }

    /**
     * Add a connection to its parent to every node.
     *
     * @since 0.1.0
     *
     * @param Node[] $ast Array of AST nodes.
     *
     * @return Node[] AST with added parent connections.
     */
    private function addConnections(array $ast): array
    {
        static $traverser = null;

        if (null === $traverser) {
            $traverser = new NodeTraverser();
            $traverser->addVisitor(new NodeConnector(NodeConnector::STORE_NONE));
        }

        return $traverser->traverse($ast);
    }

    /**
     * Normalize the root folder.
     *
     * @since 0.1.0
     *
     * @param string $root Root folder to normalize.
     *
     * @return string Normalized root folder.
     */
    private function normalizeRoot(string $root): string
    {
        return rtrim(realpath($root), '/') . '/';
    }
}
