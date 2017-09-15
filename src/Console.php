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

use Robo\Tasks;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class Console.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
final class Console extends Tasks
{
    const SOURCE_ROOT = __DIR__ . '/../vendor/johnpbloch/wordpress-core/';
    const CACHE_ROOT = __DIR__ . '/../cache/';
    const GENERATORS = [
        Generator\ConstantsReference::class,
    ];

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

        $parser = new Parser\FilesystemParser(
            self::SOURCE_ROOT,
            new Parser\CachedParser(
                self::SOURCE_ROOT,
                self::CACHE_ROOT,
                new Parser\FileBasedParser(self::SOURCE_ROOT)
            ),
            $this->output()
        );

        $ast = $parser->parse($file);

        foreach (self::GENERATORS as $generatorClass) {
            /** @var Generator $generator */
            $generator = new $generatorClass($this->output);
            $generator->generate($ast);
        }

        $end = getrusage();

        $this->printTiming($start, $end);
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
