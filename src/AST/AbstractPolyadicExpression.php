<?php
namespace Icecave\Dialekt\AST;

/**
 * A base class providing common functionality for polyadic operators.
 */
abstract class AbstractPolyadicExpression extends AbstractExpression implements
    ExpressionInterface
{
    /**
     * @param ExpressionInterface $child,... One or more children to add to this operator.
     */
    public function __construct()
    {
        $this->children = array();

        foreach (func_get_args() as $child) {
            $this->add($child);
        }
    }

    /**
     * Add a child expression to this operator.
     *
     * @param ExpressionInterface $expression The expression to add.
     */
    public function add(ExpressionInterface $expression)
    {
        $this->children[] = $expression;
    }

    /**
     * Fetch an array of this operator's children.
     *
     * @return array<ExpressionInterface> The operator's child expressions.
     */
    public function children()
    {
        return $this->children;
    }

    private $children;
}
