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

use PhpParser\NodeAbstract;

/**
 * Abstract class Folder.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
abstract class Folder extends NodeAbstract
{
    /**
     * Folders attached to this filesystem.
     *
     * @since 0.1.0
     *
     * @var Folder[]
     */
    public $folders = [];

    /**
     * Files attached to this filesystem.
     *
     * @since 0.1.0
     *
     * @var File[]
     */
    public $files = [];

    /**
     * Add a folder to the filesystem.
     *
     * @since 0.1.0
     *
     * @param Folder $folder Folder to add.
     *
     * @return Folder Modified folder.
     */
    public function addFolder(Folder $folder): Folder
    {
        $this->folders[] = $folder;

        return $this;
    }

    /**
     * Add a file to the filesystem.
     *
     * @since 0.1.0
     *
     * @param File $file File to add.
     *
     * @return Folder Modified folder.
     */
    public function addFile(File $file): Folder
    {
        $this->files[] = $file;

        return $this;
    }

    /**
     * Gets the names of the sub nodes.
     *
     * @since 0.1.0
     *
     * @return array Names of sub nodes.
     */
    public function getSubNodeNames()
    {
        return ['folders', 'files'];
    }
}
