<?php
namespace Icecave\Dialekt\Expression;

class LogicalOr extends AbstractCompoundExpression
{
    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitLogicalOr($this);
    }
}
