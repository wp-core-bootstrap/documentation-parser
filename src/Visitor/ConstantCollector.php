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
use WPCoreBootstrap\DocumentationParser\Entry\Location;
use WPCoreBootstrap\DocumentationParser\Entry;

/**
 * Class ConstantCollector.
 *
 * @since   0.1.0
 *
 * @package WPCoreBootstrap\DocumentationParser\Visitor
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
final class ConstantCollector extends BaseVisitor
{
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
        $this->constants = [];

        return parent::beforeTraverse($nodes);
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
        if ($result = $this->matchesFunction($node, 'define', 'name', 'value')) {
            $this->addDefineConstantEntry($node, $this->getString($result['name']->value), $result['value']->value);
        }

        return parent::enterNode($node);
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

    /**
     * Add DefineConstant entry.
     *
     * @since 0.1.0
     *
     * @param Node   $node  Node to parse.
     * @param string $name  Name of the constant.
     * @param Node   $value Value node.
     */
    private function addDefineConstantEntry(Node $node, string $name, Node $value)
    {
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
        );

        $comment = $node->getDocComment() ?? $this->getParentComment($node);

        if ($comment) {
            $constant->withComment($comment);
        }

        $this->constants[$name] = $constant;
    }
}
