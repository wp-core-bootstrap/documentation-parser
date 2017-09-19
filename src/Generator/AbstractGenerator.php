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

namespace WPCoreBootstrap\DocumentationParser\Generator;

use Robo\Common\IO;
use Robo\Contract\IOAwareInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WPCoreBootstrap\DocumentationParser\Entry\HasName;
use WPCoreBootstrap\DocumentationParser\Generator;
use WPCoreBootstrap\DocumentationParser\Entry;

/**
 * Abstract class AbstractGenerator.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser\Generator
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
abstract class AbstractGenerator implements Generator, IOAwareInterface
{
    use IO;

    const SOURCE_LINK_ROOT = 'https://github.com/WordPress/WordPress/blob/master/';

    /**
     * Instantiate an AbstractGenerator object.
     *
     * @since 0.1.0
     *
     * @param OutputInterface $output Output interface to use.
     */
    public function __construct(OutputInterface $output)
    {
        $this->setOutput($output);
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
    protected function getSourceLink(Entry\Location $location): string
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
     * Sort an array of entries based on name.
     *
     * @since 0.1.0
     *
     * @param Entry[] $entries Array of entries to sort.
     */
    protected function sortEntriesOnName(array &$entries)
    {
        usort($entries, function ($entryA, $entryB) {
            /** @var Entry $entryA */
            /** @var Entry $entryB */
            if (! $entryA instanceof HasName
                || ! $entryB instanceof HasName) {
                return 0;
            }

            return strcmp($entryA->getName(), $entryB->getName());
        });
    }


    /**
     * Sort an array of locations based on the folder and file hierarchy.
     *
     * @since 0.1.0
     *
     * @param Entry\Location[] $locations Array of locations to sort.
     */
    protected function sortLocationsOnFile(array &$locations)
    {
        usort($locations, function ($locationA, $locationB) {
            /** @var Entry\Location $locationA */
            /** @var Entry\Location $locationB */
            $hierarchyA = explode('/', $locationA->getFile());
            $hierarchyB = explode('/', $locationB->getFile());

            while (! empty($hierarchyA) || ! empty($hierarchyB)) {
                if (count($hierarchyA) === 1 && count($hierarchyB) > 1) {
                    return -1;
                }
                if (count($hierarchyB) === 1 && count($hierarchyA) > 1) {
                    return 1;
                }
                if (count($hierarchyA) === 1 && count($hierarchyB) === 1) {
                    return strcmp($hierarchyA[0], $hierarchyB[0]);
                }

                $comparison = strcmp($hierarchyA[0], $hierarchyB[0]);

                if ($comparison !== 0) {
                    return $comparison;
                }

                array_shift($hierarchyA);
                array_shift($hierarchyB);
            }

            return 0;
        });
    }

    /**
     * Group sort the locations for entries.
     *
     * @since 0.1.0
     *
     * @param Entry[]    $entries    Array of entries to sort.
     * @param array|null $requireKey Optional. Whether to require one of a set of keys to be present.
     */
    protected function groupSortLocations(array &$entries, array $requireKey = null)
    {
        array_map(function (&$entry) {
            /** @var Entry\Constant $entry */
            $entry->locations = $this->groupByLocationType($entry->locations);
        }, $entries);

        if ($requireKey) {
            $entries = array_filter($entries, function ($entry) use ($requireKey) {
                return ! empty(array_intersect($requireKey, array_keys($entry->locations)));
            });
        }

        array_map(function (&$entry) {
            /** @var Entry\Constant $entry */
            $groups = $entry->locations;
            array_map([$this, 'sortLocationsOnFile'], $groups);
            $entry->locations = array_merge(... array_values($groups));
        }, $entries);
    }

    /**
     * Group the locations by location type.
     *
     * This will add another level in front of the current array, with the FQCN as index.
     *
     * @since 0.1.0
     *
     * @param Entry\Location[] $locations Array of locations.
     *
     * @return array Grouped array of locations.
     */
    protected function groupByLocationType(array $locations)
    {
        $groupedLocations = [];

        array_map(function ($location) use (&$groupedLocations) {
            $groupedLocations[get_class($location)][] = $location;
        }, $locations);

        ksort($groupedLocations);

        return $groupedLocations;
    }
}
