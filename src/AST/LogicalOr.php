<?php
namespace Dialekt\AST;

/**
 * An AST node that represents the logical OR operator.
 */
class LogicalOr extends AbstractPolyadicExpression
{
    /**
     * Pass this node to the appropriate method on the given visitor.
     *
     * @param VisitorInterface $visitor The visitor to dispatch to.
     *
     * @return mixed The visitation result.
     */
    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitLogicalOr($this);
    }
}
