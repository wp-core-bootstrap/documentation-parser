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

namespace WPCoreBootstrap\DocumentationParser\Node;

use PhpParser\Node\Name;

/**
 * Class SubFolder.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class SubFolder extends Folder
{
    /**
     * Instantiate a SubFolder object.
     *
     * @since 0.1.0
     *
     * @param string $name Name of the sub-folder.
     * @param array  $attributes
     */
    public function __construct(string $name, array $attributes = [])
    {
        parent::__construct($attributes);
        $this->name = new Name($name);
    }
}
