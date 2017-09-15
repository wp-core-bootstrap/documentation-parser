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

use PhpParser\Node;
use PhpParser\NodeTraverser;
use Robo\Tasks;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use WPCoreBootstrap\DocumentationParser\Entry;
use WPCoreBootstrap\DocumentationParser\Visitor\ConstantCollector;

/**
 * Class RoboFile.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
final class RoboFile extends Tasks
{
    const SOURCE_ROOT = __DIR__ . '/../vendor/johnpbloch/wordpress-core/';
    const CACHE_ROOT = __DIR__ . '/../cache/';
    const SOURCE_LINK_ROOT = 'https://github.com/WordPress/WordPress/blob/master/';

    /**
     * Generates the documentation from the WordPress Core files
     *
     * @since 0.1.0
     *
     * @param string $file Optional. File to parse.
     *
     * @throws \InvalidArgumentException If the file names could not be processed.
     * @throws \RuntimeException If a file could not be parsed.
     */
    public function docsGenerate(string $file = null)
    {
        $this->io()->title('Generating documentation');

        $start = getrusage();

        $parser = new FilesystemParser(
            self::SOURCE_ROOT,
            new CachedParser(
                self::SOURCE_ROOT,
                self::CACHE_ROOT,
                new FileBasedParser(self::SOURCE_ROOT)
            ),
            $this->output()
        );

        $ast = $parser->parse($file);

        $this->say('Collecting constants...');
        $constants = $this->collectConstants($ast);

        $this->say('Dumping constants into markdown file (constants.md)...');
        file_put_contents('docs/constants.md', $this->dumpConstantsMarkdown($constants));

        $end = getrusage();

        $this->printTiming($start, $end);
    }

    /**
     * Collect the constants used in a given abstract syntax tree.
     *
     * @since 0.1.0
     *
     * @param Node[] $ast Array of AST nodes.
     *
     * @return Node[]
     */
    private function collectConstants(array $ast)
    {
        static $traverser = null;
        static $constantCollector = null;

        if (null === $traverser) {
            $traverser         = new NodeTraverser();
            $constantCollector = new ConstantCollector();
            $traverser->addVisitor($constantCollector);
        }

        $traverser->traverse($ast);

        return $constantCollector->getConstants();
    }

    /**
     * Dump a markdown file describing the constants.
     *
     * @since 0.1.0
     *
     * @param Node[] $constants Constants to describe.
     *
     * @return string Markdown document.
     */
    private function dumpConstantsMarkdown(array $constants)
    {
        $output = "# Constants\n";
        foreach ($constants as $constant) {
            /** @var Entry\Constant $constant */
            $description = $constant->getShortDescription();
            $output      .= sprintf(
                "* **`%s`%s**\n\n",
                $constant->name,
                ! empty($description) ? " - {$description}" : ''
            );

            foreach ($constant->locations as $location) {
                $output .= sprintf(
                    "\t%s in [%s](%s) and set to %s\n\n",
                    $location instanceof Entry\Location\Declaration ? 'Declared' : 'Used',
                    $location->render(),
                    $this->getSourceLink($location),
                    $location->renderValue()
                );
            }
        }

        return $output;
    }

    /**
     * Get a link URL to the WordPress source files.
     *
     * @since 0.1.0
     *
     * @param Entry\Location $location Location to link to.
     *
     * @return string Link URL.
     */
    private function getSourceLink(Entry\Location $location): string
    {
        return sprintf(
            '%s%s#%s',
            self::SOURCE_LINK_ROOT,
            $location->getFile(),
            $location->getStartLine() === $location->getEndLine()
                ? "L{$location->getStartLine()}"
                : "L{$location->getStartLine()}-L{$location->getEndLine()}"

        );
    }

    /**
     * Clears the cache.
     *
     * @since 0.1.0
     *
     * @throws \InvalidArgumentException If the file names could not be processed.
     * @throws IOException If a file could not be removed.
     */
    public function cacheClear()
    {
        $this->io()->title('Clearing cache');

        $start = getrusage();

        $finder = (new Finder())
            ->in(self::CACHE_ROOT)
            ->ignoreDotFiles(Finder::IGNORE_DOT_FILES);

        $filesystem = new Filesystem();

        $filesystem->remove($finder->files());
        $filesystem->remove($finder->directories());

        $end = getrusage();

        $this->printTiming($start, $end);
    }

    /**
     * Print the timing for a command, based on start and end times.
     *
     * Start end end time arrays need to be associative arrays produced through `getrusage()`.
     *
     * @since 0.1.0
     *
     * @param array $start Start time array.
     * @param array $end   End time array.
     */
    private function printTiming(array $start, array $end)
    {
        $this->say(
            sprintf(
                'Execution time: user %1$d ms, system %2$d ms',
                $this->resourceUsageTime($start, $end, 'utime'),
                $this->resourceUsageTime($start, $end, 'stime')
            )
        );
    }

    /**
     * Get the resource usage time in milliseconds for a given resource index.
     *
     * Start end end time arrays need to be associative arrays produced through `getrusage()`.
     *
     * @since 0.1.0
     *
     * @param array  $start Start time array.
     * @param array  $end   End time array.
     * @param string $index Resource index to use for the calculation.
     *
     * @return int Resource usage time in milliseconds.
     */
    private function resourceUsageTime(array $start, array $end, string $index): int
    {
        return ($end["ru_$index.tv_sec"] * 1000 + intval($end["ru_$index.tv_usec"] / 1000))
            - ($start["ru_$index.tv_sec"] * 1000 + intval($start["ru_$index.tv_usec"] / 1000));
    }
}
