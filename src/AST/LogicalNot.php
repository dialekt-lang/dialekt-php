<?php
namespace Icecave\Dialekt\AST;

/**
 * An AST node that represents the logical NOT operator.
 */
class LogicalNot implements ExpressionInterface
{
    /**
     * @param ExpressionInterface $child The expression being inverted by the NOT operator.
     */
    public function __construct(ExpressionInterface $child)
    {
        $this->child = $child;
    }

    /**
     * Fetch the expression being inverted by the NOT operator.
     *
     * @return ExpressionInterface The operator's child expression.
     */
    public function child()
    {
        return $this->child;
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
        return $visitor->visitLogicalNot($this);
    }

    private $child;
}
