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
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use WPCoreBootstrap\DocumentationParser\Parser;

/**
 * Class CachedParser.
 *
 * @since  0.1.0
 *
 * @author Alain Schlesser <alain.schlesser@gmail.com>
 */
final class CachedParser implements Parser
{

    /**
     * Root folder of the source files.
     *
     * @since 0.1.0
     *
     * @var string
     */
    private $sourceRoot;

    /**
     * Root folder of the cached files.
     *
     * @since 0.1.0
     *
     * @var string
     */
    private $cacheRoot;

    /**
     * Parser instance to cache.
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
     * Hashes of files already parsed.
     *
     * @since 0.1.0
     *
     * @var string[]
     */
    private $hashes = [];

    /**
     * Instantiate a Parser object.
     *
     * @since 0.1.0
     *
     * @param string     $sourceRoot Root folder of the source files.
     * @param string     $cacheRoot  Root folder of the cached files.
     * @param Parser     $parser     Parser instance to cache.
     * @param Filesystem $filesystem Filesystem instance to use.
     */
    public function __construct(string $sourceRoot, string $cacheRoot, Parser $parser, Filesystem $filesystem = null)
    {
        $this->sourceRoot = $sourceRoot;
        $this->cacheRoot  = $cacheRoot;
        $this->parser     = $parser;
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    /**
     * Parse the code and return an array of AST trees.
     *
     * @since 0.1.0
     *
     * @param string $file Optional. Name (and relative path) of the file to parse.
     *
     * @return Node[] Array of abstract syntax tree nodes.
     * @throws \RuntimeException If the file could not be read.
     */
    public function parse(string $file = null): array
    {
        if (null === $file) {
            return [];
        }

        $hash = $this->getHash($file);

        if (! $this->hasCachedAST($file, $hash)) {
            $ast = $this->createCachedAST($file, $hash);
        }

        return $ast ?? $this->getCachedAST($file, $hash);
    }

    /**
     * Get the hash for a given file.
     *
     * @since 0.1.0
     *
     * @param string $file File to get the hash for.
     *
     * @return string Hash of the file.
     * @throws \RuntimeException If the file could not be read.
     */
    private function getHash(string $file): string
    {
        // Store hashes under hashed indexes, to save memory.
        $hashKey = md5($file);

        if (array_key_exists($hashKey, $this->hashes)) {
            return $this->hashes[$hashKey];
        }

        if (! $this->filesystem->exists($this->sourceRoot . $file)) {
            throw new \RuntimeException("Could not read file \"{$file}\".");
        }

        $this->hashes[$hashKey] = $hash = md5(file_get_contents($this->sourceRoot . $file));

        return $hash;
    }

    /**
     * Check whether a cached abstract syntax tree exists for a given file.
     *
     * @since 0.1.0
     *
     * @param string $file File to check the existence of the AST for.
     * @param string $hash Hash of the file.
     *
     * @return bool Whether a cached version of the abstract syntax tree was found.
     */
    private function hasCachedAST(string $file, string $hash): bool
    {
        $hashedFile = $this->getHashedFilename($file, $hash);
        return $this->filesystem->exists($this->cacheRoot . $hashedFile);
    }

    /**
     * Create the cached abstract syntax tree for a given file.
     *
     * @since 0.1.0
     *
     * @param string $file File to create the AST for.
     * @param string $hash Hash of the file.
     *
     * @return Node[] Abstract syntax tree.
     * @throws IOException If the file could not be written.
     */
    private function createCachedAST(string $file, string $hash): array
    {
        $ast        = $this->parser->parse($file);
        $astDump    = $this->createASTDump($ast);
        $hashedFile = $this->getHashedFilename($file, $hash);
        $this->filesystem->dumpFile($this->cacheRoot . $hashedFile, $astDump);
        return $ast;
    }

    /**
     * Create an encoded dump from an abstract syntax tree.
     *
     * @since 0.1.0
     *
     * @param Node[] $ast Abstract syntax tree to create the dump for.
     *
     * @return string Encoded dump.
     */
    private function createASTDump(array $ast): string
    {
        return serialize($ast);
    }

    /**
     * Get the cached abstract syntax tree for a given file.
     *
     * @since 0.1.0
     *
     * @param string $file File to get the AST for.
     * @param string $hash Hash of the file.
     *
     * @return Node[] Abstract syntax tree.
     */
    private function getCachedAST(string $file, string $hash): array
    {
        $hashedFile    = $this->getHashedFilename($file, $hash);
        $serializedAST = file_get_contents($this->cacheRoot . $hashedFile);
        return unserialize($serializedAST, ['allowed_classes' => true]);
    }

    /**
     * Get the hashed representation of a file name.
     *
     * @since 0.1.0
     *
     * @param string $file File to get the hashed representation for.
     * @param string $hash Hash to use for the file.
     *
     * @return string Hashed representation of the file.
     */
    private function getHashedFilename(string $file, string $hash): string
    {
        return "{$file}.{$hash}.serialized";
    }
}
