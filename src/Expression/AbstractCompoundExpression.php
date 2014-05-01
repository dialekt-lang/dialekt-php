<?php
namespace Icecave\Dialekt\Expression;

abstract class AbstractCompoundExpression implements ExpressionInterface
{
    public function __construct(array $children)
    {
        foreach ($children as $child) {
            if (!$child instanceof ExpressionInterface) {
                throw new InvalidArgumentException('Children must be an array of expressions.');
            }

            $this->children[] = $children;
        }
    }

    public function children()
    {
        return $this->children;
    }

    private $children;
}
