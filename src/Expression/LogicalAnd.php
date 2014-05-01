<?php
namespace Icecave\Dialekt\Expression;

class LogicalAnd extends AbstractCompoundExpression
{
    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitLogicalAnd($this);
    }
}
