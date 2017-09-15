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

namespace WPCoreBootstrap\DocumentationParser\Parser;

use PhpParser\Node;
use Robo\Common\IO;
use Robo\Contract\IOAwareInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use WPCoreBootstrap\DocumentationParser\Node\File;
use WPCoreBootstrap\DocumentationParser\Node\Folder;
use WPCoreBootstrap\DocumentationParser\Node\SubFolder;
use WPCoreBootstrap\DocumentationParser\Node\RootFolder;
use WPCoreBootstrap\DocumentationParser\Parser;

/**
 * Class FilesystemParser.
 *
 * @since  0.1.0
 *
 * @author Alain Schlesser <alain.schlesser@gmail.com>
 */
final class FilesystemParser implements Parser, IOAwareInterface
{

    use IO;

    /**
     * Root folder to start parsing from.
     *
     * @since 0.1.0
     *
     * @var string
     */
    private $root;

    /**
     * File-based parser instance to use.
     *
     * @since 0.1.0
     *
     * @var Parser
     */
    private $parser;

    /**
     * Filesystem instance to use.
     *
     * @since 0.1.0
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Instantiate a FileBasedParser object.
     *
     * @since 0.1.0
     *
     * @param string          $root   Root folder to start parsing from.
     * @param Parser          $parser File-based parser to use.
     * @param OutputInterface $output Output to use for messages.
     */
    public function __construct(string $root, Parser $parser, OutputInterface $output)
    {
        $this->filesystem = new Filesystem();
        $this->root       = realpath($root);
        $this->parser     = $parser;
        $this->setOutput($output);
    }

    /**
     * Parse the code and return an array of AST trees.
     *
     * @since 0.1.0
     *
     * @param string $file Optional. Name (and relative path) of the file to parse.
     *
     * @return Node[] Array of abstract syntax tree nodes.
     */
    public function parse(string $file = null): array
    {
        $rootFolder = new RootFolder($this->root);

        if (null !== $file) {
            $ast       = $this->parseFile($file);
            $hierarchy = explode('/', $file);
            $folder    = $rootFolder;
            $prefix    = '';
            while (count($hierarchy) > 1) {
                $subFolder = new SubFolder("{$prefix}{$hierarchy[0]}");
                $folder->addFolder($subFolder);
                $prefix = "{$prefix}{$hierarchy[0]}/";
                array_shift($hierarchy);
                $folder = $subFolder;
            }
            $folder->addFile(new File($file, $ast));
            return [$rootFolder];
        }

        $rootFolder = $this->scanFolder($this->root, $rootFolder);

        return [$rootFolder];
    }

    /**
     * Scan an individual folder.
     *
     * @since 0.1.0
     *
     * @param string $folderName Name of the folder to scan.
     * @param Folder $ast        Array of abstract syntax tree nodes to attach the folder to.
     *
     * @return Folder
     */
    private function scanFolder(string $folderName, Folder $ast)
    {
        /** @var \SplFileInfo[] $folders */
        $folders = (new Finder())
            ->in($folderName)
            ->depth('<1')
            ->directories();

        foreach ($folders as $folder) {
            $relativeFolder = $this->getRelativePath($folder->getPathname());
            if (in_array($relativeFolder, $this->getFolderBlacklist(), true)) {
                continue;
            }
            $this->say("Scanning folder \"{$relativeFolder}\"...");
            $subFolder = new SubFolder($relativeFolder);
            $subFolder = $this->scanFolder($folder->getPathname(), $subFolder);
            $ast->addFolder($subFolder);
        }

        /** @var \SplFileInfo[] $files */
        $files = (new Finder())
            ->in($folderName)
            ->name('*.php')
            ->depth('<1')
            ->files();

        foreach ($files as $file) {
            $relativeFolder = $this->getRelativePath($file->getPath());
            if (in_array($relativeFolder, $this->getFolderBlacklist(), true)) {
                continue;
            }
            $file = $relativeFolder . $file->getFilename();
            if (in_array($file, $this->getFileBlacklist(), true)) {
                continue;
            }
            $ast->addFile(new File($file, $this->parseFile($file)));
        }

        return $ast;
    }

    /**
     * Parse a single file.
     *
     * @since 0.1.0
     *
     * @param string $file File to parse.
     *
     * @return Node[] Array of abstract syntax tree nodes.
     */
    private function parseFile(string $file): array
    {
        $this->say("Parsing file \"{$file}\"...");
        return $this->parser->parse($file);
    }

    /**
     * Get a relative path from an absolute path.
     *
     * @since 0.1.0
     *
     * @param string $absolutePath Absolute path to get the relative path for.
     *
     * @return bool|string Relative path.
     */
    private function getRelativePath(string $absolutePath)
    {
        $relativePath = $this->filesystem->makePathRelative($absolutePath, $this->root);
        if (0 === strpos($relativePath, './')) {
            $relativePath = substr($relativePath, 2);
        }
        return $relativePath;
    }

    /**
     * Get the list of folders to exclude.
     *
     * @since 0.1.0
     *
     * @return array List of folders to exclude.
     */
    private function getFolderBlacklist()
    {
        return [
            'wp-content/',
            'wp-includes/ID3/',
            'wp-includes/IXR/',
            'wp-includes/Requests/',
            'wp-includes/SimplePie/',
            'wp-includes/Text/',
            'wp-includes/theme-compat/',
        ];
    }

    /**
     * Get the list of files to exclude.
     *
     * @since 0.1.0
     *
     * @return array List of files to exclude.
     */
    private function getFileBlacklist()
    {
        return [
            'wp-admin/includes/class-pclzip.php',
            'wp-admin/includes/class-ftp.php',
            'wp-includes/class-json.php',
            'wp-includes/class-simplepie.php',
        ];
    }
}
