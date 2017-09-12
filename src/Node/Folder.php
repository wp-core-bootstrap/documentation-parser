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

/**
 * Class Folder.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class Folder extends FileSystem
{
    /**
     * Name of the folder.
     *
     * @since 0.1.0
     *
     * @var string
     */
    public $name;

    /**
     * Instantiate a Folder object.
     *
     * @since 0.1.0
     *
     * @param string $name Folder name.
     * @param array  $attributes
     */
    public function __construct(string $name, array $attributes = [])
    {
        parent::__construct($attributes);
        $this->name = $name;
    }

    /**
     * Get the sub-node names that this node contains.
     *
     * @since 0.1.0
     *
     * @return array
     */
    public function getSubNodeNames(): array
    {
        return ['name'];
    }
}