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
        // define ( <name>, <value> )
        if ($result = $this->matchesFunction($node, 'define', 'name', 'value')) {
            $entry    = new Entry\DefineConstant($this->getString($result['name']->value));
            $location = $this->createDeclaration($node, $result['value']->value);
            $this->addConstantEntry($entry, $location, $this->getComment($node));
        }

        // class <parent> { const <name> = <value>; }
        if ($node instanceof Node\Stmt\ClassConst) {
            $name = $node->consts[0]->name;

            $parent = $node->getAttribute('parent');
            if ($parent && $parent instanceof Node\Stmt\Class_) {
                $name = "{$parent->name}::{$name}";
            }

            $entry    = new Entry\ConstConstant($name);
            $location = $this->createDeclaration($node, $node->consts[0]->value);

            $this->addConstantEntry($entry, $location, $this->getComment($node));
        }

        // <name>
        if ($node instanceof Node\Expr\ConstFetch) {
            $entry    = new Entry\Constant((string)$node->name);
            $location = $this->createUsage($node);
            $this->addConstantEntry($entry, $location);
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
     * Add constant entry.
     *
     * @since 0.1.0
     *
     * @param Entry    $entry    Entry to be added as a constant.
     * @param Location $location Location that the constant was found in.
     * @param Doc      $comment  Comment to attach to the entry.
     */
    private function addConstantEntry(Entry $entry, Location $location, Doc $comment = null)
    {
        $constant = array_key_exists($entry->getName(), $this->constants)
            ? $this->constants[$entry->getName()]
            : $entry;

        $constant = $constant->withLocation($location);

        if ($comment) {
            $constant = $constant->withComment($comment);
        }

        $this->constants[$constant->getName()] = $constant;
    }

    /**
     * Create a new Declaration location.
     *
     * @since 0.1.0
     *
     * @param Node $node  Node to create the Declaration location for.
     * @param Node $value Value used in the declaration.
     *
     * @return Location\Declaration
     */
    private function createDeclaration(Node $node, Node $value)
    {
        return new Location\Declaration(
            $this->file,
            (int)$node->getAttribute('startLine', '-1'),
            (int)$node->getAttribute('endLine', '-1'),
            $value
        );
    }

    /**
     * Create a new Usage location.
     *
     * @since 0.1.0
     *
     * @param Node $node Node to create the Usage location for.
     *
     * @return Location\Usage
     */
    private function createUsage(Node $node)
    {
        return new Location\Usage(
            $this->file,
            (int)$node->getAttribute('startLine', '-1'),
            (int)$node->getAttribute('endLine', '-1')
        );
    }
}
