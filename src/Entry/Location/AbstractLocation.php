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

namespace WPCoreBootstrap\DocumentationParser\Entry\Location;

use WPCoreBootstrap\DocumentationParser\Entry\Location;

/**
 * Class Usage.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser\Entry
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
abstract class AbstractLocation implements Location
{
    public $file;
    public $startLine;
    public $endLine;

    /**
     * Instantiate an AbstractLocation object.
     *
     * @since 0.1.0
     *
     * @param string $file      Name of the file that the entry was found in.
     * @param int    $startLine Starting line number.
     * @param int    $endLine   Ending line number.
     */
    public function __construct(string $file, int $startLine, int $endLine)
    {
        $this->file      = $file;
        $this->startLine = $startLine;
        $this->endLine   = $endLine;
    }

    /**
     * Get the file of the location.
     *
     * @since 0.1.0
     *
     * @return string File of the location.
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * Get the starting line of the location.
     *
     * @since 0.1.0
     *
     * @return int Start line.
     */
    public function getStartLine(): int
    {
        return $this->startLine;
    }

    /**
     * Get the ending line of the location.
     *
     * @since 0.1.0
     *
     * @return int Ending line of the location.
     */
    public function getEndLine(): int
    {
        return $this->endLine;
    }

    /**
     * Render the location.
     *
     * The location takes the format  `<relative folder(s)>/<file name>:<staring line>[-<ending line>]`.
     *
     * @since 0.1.0
     *
     * @return string
     */
    public function render(): string
    {
        return sprintf(
            '%s:%s',
            $this->file,
            $this->startLine === $this->endLine
                ? $this->startLine
                : "{$this->startLine}-{$this->endLine}"
        );
    }
}
