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

namespace WPCoreBootstrap\DocumentationParser;

use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class CachedParserTest.
 *
 * @since  0.1.0
 *
 * @author Alain Schlesser <alain.schlesser@gmail.com>
 */
class CachedParserTest extends TestCase
{

    /**
     * Folder in which to store the test cache files.
     */
    const CACHE_ROOT = __DIR__ . '/cache/';

    /**
     * Set up tests.
     *
     * @since 0.1.0
     */
    public function setUp()
    {
        $this->clearTestCache();
    }

    /**
     * Tear down tests.
     *
     * @since 0.1.0
     */
    public function tearDown()
    {
        $this->clearTestCache();
    }

    /**
     * Test whether the class can be instantiated.
     *
     * @since 0.1.0
     */
    public function testClassInstantiation()
    {
        $mockParser = $this->createMock(Parser::class);
        $object     = new CachedParser(RoboFile::SOURCE_ROOT, self::CACHE_ROOT, $mockParser);
        $this->assertInstanceOf(
            'WPCoreBootstrap\DocumentationParser\Parser',
            $object
        );
    }

    /**
     * Test basic cached parsing.
     *
     * @since 0.1.0
     */
    public function testParsing()
    {
        $comment    = <<<EOT
/**
 * This method does something.
 *
 * @param SomeClass And takes a parameter
 */
EOT;
        $factory    = new BuilderFactory();
        $sampleAST  = $factory->namespace('TestNamespace')
            ->addStmt(
                $factory->method('someMethod')
                    ->makePublic()
                    ->setReturnType('bool')
                    ->addParam($factory->param('someParam')->setTypeHint('SomeClass'))
                    ->setDocComment($comment)
            )->getNode();
        $mockParser = $this->createMock(Parser::class);
        $mockParser->method('parse')
            ->willReturn([$sampleAST]);

        $parser = new CachedParser(RoboFile::SOURCE_ROOT, self::CACHE_ROOT, $mockParser);

        // The first request will be uncached, and store the cache in a file.
        $uncachedAST = $parser->parse('index.php');
        /** @var ClassMethod $node */
        $uncachedNode = $uncachedAST[0]->stmts[0];

        $this->assertEquals('someMethod', $uncachedNode->name);

        // The second request will find the cached file, and produce the output from that file.
        $cachedAST = $parser->parse('index.php');
        /** @var ClassMethod $node */
        $cachedNode = $cachedAST[0]->stmts[0];

        $this->assertEquals('someMethod', $cachedNode->name);
        $this->assertEquals($cachedNode, $uncachedNode);
    }

    /**
     * Clear the test cache folder.
     *
     * @since 0.1.0
     */
    private function clearTestCache()
    {
        $finder = (new Finder())
            ->in(self::CACHE_ROOT)
            ->ignoreDotFiles(Finder::IGNORE_DOT_FILES);

        $filesystem = new Filesystem();

        $filesystem->remove($finder->files());
        $filesystem->remove($finder->directories());
    }
}
