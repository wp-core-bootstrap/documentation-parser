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
use PhpParser\NodeVisitorAbstract;

/**
 * Class NodeConnector.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser\Visitor
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
final class NodeConnector extends NodeVisitorAbstract
{
    const STORE_NONE = 0;
    const STORE_PARENT = 1;
    const STORE_SIBLINGS = 2;

    /**
     * Current stack.
     *
     * @since 0.1.0
     *
     * @var Node[]
     */
    private $stack;

    /**
     * Previous node.
     *
     * @since 0.1.0
     *
     * @var Node
     */
    private $previous;

    /**
     * Whether to store parent relations.
     *
     * @since 0.1.0
     *
     * @var bool
     */
    private $storeParent = false;

    /**
     * Whether to store sibling relations.
     *
     * @since 0.1.0
     *
     * @var bool
     */
    private $storeSiblings = false;

    /**
     * Instantiate a NodeConnector object.
     *
     * @since 0.1.0
     *
     * @param int $mode Mode to operate in.
     *
     * @throws \RuntimeException If siblings are stored without parents.
     */
    public function __construct(int $mode = self::STORE_PARENT)
    {
        $this->storeParent   = ($mode & self::STORE_PARENT) === self::STORE_PARENT;
        $this->storeSiblings = ($mode & self::STORE_SIBLINGS) === self::STORE_SIBLINGS;

        if ($this->storeSiblings && ! $this->storeParent) {
            throw new \RuntimeException(
                'NodeConnector cannot store siblings without storing parent nodes.'
            );
        }
    }

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
        $this->storeParent && $this->stack = [];
        $this->storeSiblings && $this->prev = null;

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
        if ($this->storeParent
            && ! empty($this->stack)) {
            $node->setAttribute('parent', $this->stack[count($this->stack) - 1]);
        }

        if ($this->storeSiblings
            && $this->previous
            && $this->previous->getAttribute('parent') == $node->getAttribute('parent')) {
            $node->setAttribute('previous', $this->previous);
            $this->previous->setAttribute('next', $node);
        }

        $this->storeParent && $this->stack[] = $node;

        return null;
    }


    /**
     * Called when leaving a node.
     *
     * Return value semantics:
     *  * null
     *        => $node stays as-is
     *  * NodeTraverser::REMOVE_NODE
     *        => $node is removed from the parent array
     *  * NodeTraverser::STOP_TRAVERSAL
     *        => Traversal is aborted. $node stays as-is
     *  * array (of Nodes)
     *        => The return value is merged into the parent array (at the position of the $node)
     *  * otherwise
     *        => $node is set to the return value
     *
     * @param Node $node Node
     *
     * @return null|false|int|Node|Node[] Node
     */
    public function leaveNode(Node $node)
    {
        $this->storeSiblings && $this->previous = $node;
        $this->storeParent && array_pop($this->stack);

        return null;
    }
}
