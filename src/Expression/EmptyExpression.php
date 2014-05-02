<?php
namespace Icecave\Dialekt\Expression;

class EmptyExpression implements ExpressionInterface
{
    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitEmptyExpression($this);
    }
}
