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
use PhpParser\ParserFactory;

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
     * @param string $file Name (and relative path) of the file to parse.
     *
     * @return Node[] Array of abstract syntax tree nodes.
     */
    public function parse(string $file): array
    {
        $source = file_get_contents($this->root . $file);
        $parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP5);
        return $parser->parse($source);
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
