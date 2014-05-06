<?php
namespace Icecave\Dialekt\AST;

/**
 * Represents a pattern match expression.
 */
class Pattern implements ExpressionInterface
{
    /**
     * @param PatternChildInterface $child,... One or more pattern literals or placeholders.
     */
    public function __construct()
    {
        $this->children = array();

        foreach (func_get_args() as $child) {
            $this->add($child);
        }
    }

    /**
     * Add a child to this node.
     *
     * @param PatternChildInterface $child The child to add.
     */
    public function add(PatternChildInterface $child)
    {
        $this->children[] = $child;
    }

    /**
     * Fetch an array of this node's children.
     *
     * @return array<PatternChildInterface> The node's children.
     */
    public function children()
    {
        return $this->children;
    }

    /**
     * Pass this node to the appropriate method on the given visitor.
     *
     * @param VisitorInterface $visitor The visitor to dispatch to.
     *
     * @return mixed The visitation result.
     */
    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitPattern($this);
    }

    private $children;
}
