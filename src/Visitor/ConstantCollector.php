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

namespace WPCoreBootstrap\DocumentationParser\Visitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeVisitorAbstract;
use WPCoreBootstrap\DocumentationParser\Entry\Location;
use WPCoreBootstrap\DocumentationParser\Entry;
use WPCoreBootstrap\DocumentationParser\Node\File;

/**
 * Class ConstantCollector.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser\Visitor
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
final class ConstantCollector extends NodeVisitorAbstract
{
    /**
     * Name of the file.
     *
     * @since 0.1.0
     *
     * @var string
     */
    private $file;

    /**
     * Collected constants.
     *
     * @var Entry\Constant[]
     *
     * @since 0.1.0
     */
    private $constants;

    /**
     * Called once before traversal.
     *
     * Return value semantics:
     *  * null:      $nodes stays as-is
     *  * otherwise: $nodes is set to the return value
     *
     * @param Node[] $nodes Array of nodes
     *
     * @return null|Node[] Array of nodes
     */
    public function beforeTraverse(array $nodes)
    {
        $this->file      = '<unknown>';
        $this->constants = [];

        return null;
    }

    /**
     * Called when entering a node.
     *
     * Return value semantics:
     *  * null
     *        => $node stays as-is
     *  * NodeTraverser::DONT_TRAVERSE_CHILDREN
     *        => Children of $node are not traversed. $node stays as-is
     *  * NodeTraverser::STOP_TRAVERSAL
     *        => Traversal is aborted. $node stays as-is
     *  * otherwise
     *        => $node is set to the return value
     *
     * @param Node $node Node
     *
     * @return null|int|Node Node
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof File) {
            $this->file = $node->name;
            return null;
        }

        if ($node instanceof Expr\FuncCall && isset($node->name->parts[0]) && $node->name->parts[0] === 'define') {
            $name = $node->args[0]->value->value ?? null;

            if (null === $name) {
                return null;
            }

            $value = $node->args[1]->value ?? null;

            $constant = array_key_exists($name, $this->constants)
                ? $this->constants[$name]
                : new Entry\DefineConstant($name);

            $constant = $constant->withLocation(
                new Location\Declaration(
                    $this->file,
                    (int)$node->getAttribute('startLine', '-1'),
                    (int)$node->getAttribute('endLine', '-1'),
                    $value
                )
            )->withComment($node->getDocComment());

            $this->constants[$name] = $constant;
        }

        return null;
    }

    /**
     * Get the result of the collection run.
     *
     * @since 0.1.0
     *
     * @return Entry\Constant[]
     */
    public function getConstants(): array
    {
        return $this->constants;
    }
}
