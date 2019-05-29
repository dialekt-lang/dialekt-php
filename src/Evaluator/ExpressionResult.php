<?php
namespace Dialekt\Evaluator;

use Dialekt\AST\ExpressionInterface;

/**
 * The result for an invidiual expression in the AST.
 */
class ExpressionResult
{
    /**
     * @param ExpressionInterface $expression    The expression to which this result applies.
     * @param boolean             $isMatch       True if the expression matched the tag set; otherwise, false.
     * @param array<string>       $matchedTags   The set of tags that matched.
     * @param array<string>       $unmatchedTags The set of tags that did not match.
     */
    public function __construct(
        ExpressionInterface $expression,
        $isMatch,
        array $matchedTags,
        array $unmatchedTags
    ) {
        $this->expression = $expression;
        $this->isMatch = $isMatch;
        $this->matchedTags = $matchedTags;
        $this->unmatchedTags = $unmatchedTags;
    }

    /**
     * Fetch the expression to which this result applies.
     *
     * @return ExpressionInterface The expression to which this result applies.
     */
    public function expression()
    {
        return $this->expression;
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
     * Fetch the set of tags that matched.
     *
     * @param array<string> The set of tags that matched.
     */
    public function matchedTags()
    {
        return $this->matchedTags;
    }

    /**
     * Fetch set of tags that did not match.
     *
     * @param array<string> The set of tags that did not match.
     */
    public function unmatchedTags()
    {
        return $this->unmatchedTags;
    }

    private $expression;
    private $isMatch;
    private $matchedTags;
    private $unmatchedTags;
}
