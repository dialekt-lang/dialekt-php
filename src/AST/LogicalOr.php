<?php
namespace Icecave\Dialekt\AST;

/**
 * The logical OR operator.
 */
class LogicalOr extends AbstractPolyadicOperator
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
