<?php
namespace Icecave\Dialekt\Expression;

abstract class AbstractCompoundExpression implements ExpressionInterface
{
    public function __construct()
    {
        foreach (func_get_args() as $child) {
            $this->add($child);
        }
    }

    public function add(ExpressionInterface $expression)
    {
        $this->children[] = $expression;
    }

    public function children()
    {
        return $this->children;
    }

    private $children;
}
