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

use PhpParser\Node;
use PhpParser\NodeAbstract;

/**
 * Class File.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class File extends NodeAbstract
{
    /**
     * Name of the file.
     *
     * @since 0.1.0
     *
     * @var Node
     */
    public $name;

    /**
     * Abstract syntax tree of the file's source.
     *
     * @since 0.1.0
     *
     * @var Node[]
     */
    public $source;

    /**
     * Instantiate a File object.
     *
     * @since 0.1.0
     *
     * @param string $name File name.
     * @param array  $attributes
     */
    public function __construct(string $name, array $source, array $attributes = [])
    {
        parent::__construct($attributes);
        $this->source = $source;
        $this->name   = new Node\Name($name);
    }

    /**
     * Gets the names of the sub nodes.
     *
     * @return array Names of sub nodes
     */
    public function getSubNodeNames()
    {
        return ['name', 'source'];
    }
}
