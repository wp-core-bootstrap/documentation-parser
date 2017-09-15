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

/**
 * Interface Location.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser\Entry
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
interface Location
{
    /**
     * Get the file of the location.
     *
     * @since 0.1.0
     *
     * @return string File of the location.
     */
    public function getFile(): string;

    /**
     * Get the starting line of the location.
     *
     * @since 0.1.0
     *
     * @return int Start line.
     */
    public function getStartLine(): int;

    /**
     * Get the ending line of the location.
     *
     * @since 0.1.0
     *
     * @return int Ending line of the location.
     */
    public function getEndLine():int;

    /**
     * Render the location.
     *
     * The location takes the format  `<relative folder(s)>/<file name>:<staring line>[-<ending line>]`.
     *
     * @since 0.1.0
     *
     * @return string
     */
    public function render(): string;
}
