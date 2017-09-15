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
 * Trait Value.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser\Entry
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
trait Value
{

    /**
     * Value node that the constant was set to.
     *
     * @since 0.1.0
     *
     * @var Node
     */
    public $value;

    /**
     * Get the value node.
     *
     * @since 0.1.0
     *
     * @return Node
     */
    public function getValue(): Node
    {
        return $this->value;
    }

    /**
     * Render the value node.
     *
     * @since 0.1.0
     *
     * @return string Rendered value.
     */
    public function renderValue(): string
    {
        $type = $this->value->getType();
        switch ( $type ) {
            case 'Scalar_String':
                return "string `\"{$this->value->value}\"`";
            default:
                return $type;
        }
    }
}
