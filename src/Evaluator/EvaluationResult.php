<?php
namespace Icecave\Dialekt\Evaluator;

use Icecave\Dialekt\AST\ExpressionInterface;
use SplObjectStorage;

/**
 * The overall result of the evaluation of an expression.
 */
class EvaluationResult
{
    /**
     * @param boolean $isMatch True if the expression matched the tag set; otherwise, false.
     * @param array<ExpressionResult> The individual sub-expression results.
     */
    public function __construct($isMatch, array $expressionResults)
    {
        $this->isMatch = $isMatch;
        $this->expressionResults = new SplObjectStorage;

        foreach ($expressionResults as $result) {
            $this->expressionResults[$result->expression()] = $result;
        }
    }

    /**
     * Indicates whether or not the expression matched the tag set.
     *
     * @return boolean True if the expression matched the tag set; otherwise, false.
     */
    public function isMatch()
    {
        return $this->isMatch;
    }

    /**
     * Fetch the result for an individual expression node from the AST.
     *
     * @param ExpressionInterface $expression The expression for which the result is fetched.
     *
     * @return ExpressionResult The result for the given expression.
     */
    public function expressionResult(ExpressionInterface $expression)
    {
        return $this->expressionResults[$expression];
    }

    private $isMatch;
    private $expressionResults;
}
