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

namespace WPCoreBootstrap\DocumentationParser\Entry\Location;

use PhpParser\Node;
use WPCoreBootstrap\DocumentationParser\Entry\HasValue;
use WPCoreBootstrap\DocumentationParser\Entry\Value;

/**
 * Class Declaration.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser\Entry
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class Declaration extends AbstractLocation implements HasValue
{

    use Value;

    /**
     * Instantiate an AbstractLocation object.
     *
     * @since 0.1.0
     *
     * @param string $file      Name of the file that the entry was found in.
     * @param int    $startLine Starting line number.
     * @param int    $endLine   Ending line number.
     * @param Node   $value     Optional. Value node it is set to.
     */
    public function __construct(string $file, int $startLine, int $endLine, Node $value = null)
    {
        parent::__construct($file, $startLine, $endLine);
        $this->value = $value;
    }
}
