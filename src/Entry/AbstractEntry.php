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

use PhpParser\Comment\Doc;
use WPCoreBootstrap\DocumentationParser\Entry;

/**
 * Abstract class AbstractEntry.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser\Entry
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
abstract class AbstractEntry implements Entry
{
    /**
     * Locations in the source files that the entry was found in.
     *
     * @since 0.1.0
     *
     * @var Location[]
     */
    public $locations = [];

    /**
     * Doc-blocks that were associated with the entry.
     *
     * @since 0.1.0
     *
     * @var string[]
     */
    public $comments = [];

    /**
     * Add a reference to the source file to the entry.
     *
     * @since 0.1.0
     *
     * @param Location $location Location to add to the entry.
     *
     * @return Entry
     */
    public function withLocation(Location $location): Entry
    {
        $this->locations[] = $location;

        return $this;
    }

    /**
     * Add a comment block to the entry.
     *
     * @since 0.1.0
     *
     * @param Doc|string $comment Comment to add to the entry.
     *
     * @return Entry
     */
    public function withComment($comment): Entry
    {
        if ($comment instanceof Doc) {
            $comment = $comment->getReformattedText();
        }

        if (! empty($comment)) {
            $this->comments[] = $comment;
        }

        return $this;
    }

    /**
     * Get the short description of the doc block.
     *
     * @since 0.1.0
     *
     * @return string
     */
    public function getShortDescription(): string
    {
        if (empty($this->comments)) {
            return '';
        }

        $descriptions = [];
        foreach ($this->comments as $comment) {
            $matches = [];
            if (1 !== preg_match(
                    '/(?:\/\*\*\s*)(?:\*\s*)*(?<short>.*?)(?:(?:\s*\*\/)?(?:\n)|((?:\s*\*\/)))/',
                    $comment,
                    $matches
                )) {
                continue;
            }

            if (! array_key_exists('short', $matches)
                || empty(trim($matches['short']))) {
                continue;
            }

            $descriptions[] = trim($matches['short']);
        }

        if (empty($descriptions)) {
            return '';
        }

        return implode(' | ', $descriptions);
    }
}
