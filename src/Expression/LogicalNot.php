<?php
namespace Icecave\Dialekt\Expression;

class LogicalNot implements ExpressionInterface
{
    public function __construct(ExpressionInterface $child)
    {
        $this->child = $child;
    }

    public function child()
    {
        return $this->child;
    }

    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitLogicalNot($this);
    }

    private $child;
}
