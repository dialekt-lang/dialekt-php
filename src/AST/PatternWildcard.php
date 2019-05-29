<?php
namespace Dialekt\AST;

/**
 * Represents the actual wildcard portion of a pattern expression.
 */
class PatternWildcard implements PatternChildInterface
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
        return $visitor->visitPatternWildcard($this);
    }
}
