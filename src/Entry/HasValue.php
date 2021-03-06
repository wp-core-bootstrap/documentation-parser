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

namespace WPCoreBootstrap\DocumentationParser\Entry;

use PhpParser\Node;

/**
 * Interface HasValue.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser\Entry
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
interface HasValue
{
    /**
     * Get the value node.
     *
     * @since 0.1.0
     *
     * @return Node
     */
    public function getValue(): Node;

    /**
     * Render the value node.
     *
     * @since 0.1.0
     *
     * @return string Rendered value.
     */
    public function renderValue(): string;
}
