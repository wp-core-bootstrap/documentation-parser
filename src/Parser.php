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

/**
 * Interface Parser.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
interface Parser
{
    /**
     * Parse the code and return an array of AST trees.
     *
     * @since 0.1.0
     *
     * @param string $file Name (and relative path) of the file to parse.
     *
     * @return Node[] Array of abstract syntax tree nodes.
     */
    public function parse(string $file): array;
}
