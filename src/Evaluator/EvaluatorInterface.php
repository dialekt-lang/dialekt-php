<?php
namespace Icecave\Dialekt\Evaluator;

use Icecave\Dialekt\AST\ExpressionInterface;

/**
 * Interface for expression evaluators.
 *
 * An expression evaluator checks whether a set of tags match against a certain
 * expression.
 */
interface EvaluatorInterface
{
    /**
     * Evaluate an expression against a set of tags.
     *
     * @param ExpressionInterface $expression The expression to evaluate.
     * @param mixed<string>       $tags       The set of tags to evaluate against.
     *
     * @return boolean
     */
    public function evaluate(ExpressionInterface $expression, $tags);
}
