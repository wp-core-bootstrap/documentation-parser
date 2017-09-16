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

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeVisitorAbstract;
use WPCoreBootstrap\DocumentationParser\Node\File;

/**
 * Abstract class BaseVisitor.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser\Visitor
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
abstract class BaseVisitor extends NodeVisitorAbstract
{
    /**
     * Name of the file.
     *
     * @since 0.1.0
     *
     * @var string
     */
    protected $file;

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
        $this->file = '<unknown>';

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
            $this->file = $this->getName($node);
            return null;
        }

        return null;
    }

    /**
     * Get the name of a node.
     *
     * @since 0.1.0
     *
     * @param Node $node
     *
     * @return string
     */
    protected function getName(Node $node): string
    {
        if (! isset($node->name)
            || ! $node->name instanceof Node\Name) {
            return '';
        }

        return (string)$node->name;
    }

    /**
     * Get the value for a string.
     *
     * @since 0.1.0
     *
     * @param Node $node
     *
     * @return string
     */
    protected function getString(Node $node): string
    {
        if (! isset($node->value)
            || ! is_string($node->value)) {
            return '';
        }

        return $node->value;
    }

    /**
     * Check whether a node matches a specific function name and number of arguments.
     *
     * In case the name and the number of arguments match, this returns an associative array with the arguments named
     * after the strings that were provided.
     *
     * @since 0.1.0
     *
     * @param Node     $node    Node to check.
     * @param string   $name    Name of the function to expect.
     * @param string[] ...$args One or more strings that will be used to name the results.
     *
     * @return array
     */
    protected function matchesFunction(Node $node, string $name, string ...$args): array
    {
        if (! $node instanceof Expr\FuncCall) {
            return [];
        }

        if (! $this->isNamed($node, $name)) {
            return [];
        }

        if (count($node->args) !== count($args)) {
            echo 'No argument nodes. ' . count($node->args) . ' vs ' . count($args) . "\n";
            return [];
        }

        $result = [];
        $values = $node->args;
        while (count($args) > 0) {
            $name          = array_shift($args);
            $value         = array_shift($values);
            $result[$name] = $value;
        }

        return $result;
    }

    /**
     * Check whether the node has a specific name.
     *
     * @since 0.1.0
     *
     * @param Node   $node Node to check.
     * @param string $name Name that needs to be matched.
     *
     * @return bool Whether the node has the specified name.
     */
    protected function isNamed(Node $node, string $name): bool
    {
        return $this->getName($node) === $name;
    }

    /**
     * Return the doc-block of the parent element.
     *
     * @since 0.1.0
     *
     * @param Node $node Node to return the parent comment for.
     *
     * @return null|Doc Parent comment or null if none found.
     */
    protected function getParentComment(Node $node)
    {
        $parent = $node->getAttribute('parent');

        if (! $parent instanceof Node\Stmt\If_) {
            return null;
        }

        $comment = $parent->getDocComment();

        return $comment instanceof Doc ? $comment : null;
    }
}
