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

use PhpParser\Comment\Doc;
use PHPUnit\Framework\TestCase;

/**
 * Class FileBasedParserTest.
 *
 * @since  0.1.0
 *
 * @author Alain Schlesser <alain.schlesser@gmail.com>
 */
class FileBasedParserTest extends TestCase
{

    /**
     * Test whether the class can be instantiated.
     *
     * @since 0.1.0
     */
    public function testClassInstantiation()
    {
        $object = new FileBasedParser(RoboFile::SOURCE_ROOT);
        $this->assertInstanceOf(
            'WPCoreBootstrap\DocumentationParser\Parser',
            $object
        );
    }

    /**
     * Test basic parsing.
     *
     * @since 0.1.0
     */
    public function testParsing()
    {
        $parser  = new FileBasedParser(RoboFile::SOURCE_ROOT);
        $ast     = $parser->parse('index.php');
        $comment = <<<EOT
/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */
EOT;
        /** @var Doc $node */
        $node = $ast[0]->name->getAttributes()['comments'][0];

        $this->assertEquals($comment, $node->getText());
    }
}
