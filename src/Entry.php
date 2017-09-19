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

use PhpParser\Comment\Doc;

/**
 * Interface Entry.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
interface Entry
{
    /**
     * Add a reference to the source file to the entry.
     *
     * @since 0.1.0
     *
     * @param Entry\Location $location Location to add to the entry.
     *
     * @return Entry
     */
    public function withLocation(Entry\Location $location): Entry;

    /**
     * Add a comment block to the entry.
     *
     * @since 0.1.0
     *
     * @param Doc|string $comment Comment to add to the entry.
     *
     * @return Entry
     */
    public function withComment($comment): Entry;

    /**
     * Get the short description of the doc block.
     *
     * @since 0.1.0
     *
     * @return string
     */
    public function getShortDescription(): string;
}
