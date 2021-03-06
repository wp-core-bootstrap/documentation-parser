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

use PhpParser\Node;
use PhpParser\NodeTraverser;
use WPCoreBootstrap\DocumentationParser\Entry;
use WPCoreBootstrap\DocumentationParser\Visitor\ConstantCollector;

/**
 * Class ConstantsReference.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser\Generator
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
final class ConstantsReference extends AbstractGenerator
{
    /**
     * Generate the documentation for the given syntax tree.
     *
     * @since 0.1.0
     *
     * @param Node[] $ast Array of syntax tree nodes.
     *
     * @return void
     */
    public function generate(array $ast)
    {
        $this->say('Collecting constants...');
        $constants = $this->collectConstants($ast);

        $this->say('Dumping constants into markdown file (docs/a-constants-reference.md)...');
        file_put_contents('docs/a-constants-reference.md', $this->dumpConstantsMarkdown($constants));
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
    private function collectConstants(array $ast): array
    {
        static $traverser = null;
        static $constantCollector = null;

        if (null === $traverser) {
            $traverser         = new NodeTraverser();
            $constantCollector = new ConstantCollector();
            $traverser->addVisitor($constantCollector);
        }

        $traverser->traverse($ast);
        $constants = $constantCollector->getConstants();
        $this->sortEntriesOnName($constants);
        $this->groupSortLocations($constants, [Entry\Location\Declaration::class]);

        return $constants;
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
    private function dumpConstantsMarkdown(array $constants): string
    {
        $output = "# Constants\n";
        foreach ($constants as $constant) {
            /** @var Entry\Constant $constant */
            $output .= sprintf(
                "### `%s`\n\n",
                $constant->name
            );

            $descriptions = $constant->getShortDescription();
            if (! empty($descriptions)) {
                $descriptions = explode(' | ', $descriptions);
                foreach ($descriptions as $description) {
                    $output .= sprintf(
                        "%s\n\n",
                        ! empty($description) ? "{$description}" : ''
                    );
                }
            }

            if (! empty($constant->locations)) {
                $declarations = array_filter($constant->locations, function ($location) {
                    return $location instanceof Entry\Location\Declaration;
                });

                if (! empty($declarations)) {
                    $output .= "<details>\n\n<summary>Declarations</summary>\n\n";
                    foreach ($declarations as $declaration) {
                        /** @var Entry\Location\Declaration $declaration */
                        $output .= sprintf(
                            "Declared in [%s](%s) and set to %s\n\n",
                            $declaration->render(),
                            $this->getSourceLink($declaration),
                            $declaration->renderValue()
                        );
                    }
                    $output .= "</details>\n\n";
                }

                $usages = array_filter($constant->locations, function ($location) {
                    return $location instanceof Entry\Location\Usage;
                });

                if (! empty($usages)) {
                    $output .= "<details>\n\n<summary>Usages</summary>\n\n";
                    foreach ($usages as $usage) {
                        /** @var Entry\Location\Usage $usage */
                        $output .= sprintf(
                            "Used in [%s](%s)\n\n",
                            $usage->render(),
                            $this->getSourceLink($usage)
                        );
                    }
                    $output .= "</details>\n\n";
                }
            }

            $output .= "---\n\n";
        }

        return $output;
    }
}
