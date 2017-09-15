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
 * Class Constant.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser\Entry
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class Constant extends AbstractEntry
{

    /**
     * Name of the constant.
     *
     * @since 0.1.0
     *
     * @var string
     */
    public $name;

    /**
     * Instantiate a DefineConstant object.
     *
     * @since 0.1.0
     *
     * @param string $name Name of the defined constant.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
