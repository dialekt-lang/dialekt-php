<?php

namespace Dialekt\Evaluator;

use Dialekt\AST\EmptyExpression;
use Dialekt\AST\ExpressionInterface;
use Dialekt\AST\LogicalAnd;
use Dialekt\AST\LogicalNot;
use Dialekt\AST\LogicalOr;
use Dialekt\AST\Pattern;
use Dialekt\AST\PatternLiteral;
use Dialekt\AST\PatternWildcard;
use Dialekt\AST\Tag;
use Dialekt\AST\VisitorInterface;

class Evaluator implements EvaluatorInterface, VisitorInterface
{
    /**
     * @param bool $caseSensitive   True if tag matching should be case-sensitive; otherwise, false.
     * @param bool $emptyIsWildcard True if an empty expression matches all tags; or false to match none.
     */
    public function __construct($caseSensitive = false, $emptyIsWildcard = false)
    {
        $this->caseSensitive = $caseSensitive;
        $this->emptyIsWildcard = $emptyIsWildcard;
    }

    /**
     * Evaluate an expression against a set of tags.
     *
     * @param ExpressionInterface $expression The expression to evaluate.
     * @param mixed<string>       $tags       The set of tags to evaluate against.
     *
     * @return EvaluationResult The result of the evaluation.
     */
    public function evaluate(ExpressionInterface $expression, $tags)
    {
        $this->tags = $tags;
        $this->expressionResults = [];

        $result = new EvaluationResult(
            $expression->accept($this)->isMatch(),
            $this->expressionResults
        );

        $this->tags = null;
        $this->expressionResults = null;

        return $result;
    }

    /**
     * Visit a LogicalAnd node.
     *
     * @internal
     *
     * @param LogicalAnd $node The node to visit.
     *
     * @return mixed
     */
    public function visitLogicalAnd(LogicalAnd $node)
    {
        $matchedTags = [];
        $isMatch = true;

        foreach ($node->children() as $n) {
            $result = $n->accept($this);

            if (!$result->isMatch()) {
                $isMatch = false;
            }

            foreach ($result->matchedTags() as $tag) {
                $matchedTags[$tag] = true;
            }
        }

        $matchedTags = array_keys($matchedTags);

        return $this->expressionResults[] = new ExpressionResult(
            $node,
            $isMatch,
            $matchedTags,
            array_values(
                array_diff($this->tags, $matchedTags)
            )
        );
    }

    /**
     * Visit a LogicalOr node.
     *
     * @internal
     *
     * @param LogicalOr $node The node to visit.
     *
     * @return mixed
     */
    public function visitLogicalOr(LogicalOr $node)
    {
        $matchedTags = [];
        $isMatch = false;

        foreach ($node->children() as $n) {
            $result = $n->accept($this);

            if ($result->isMatch()) {
                $isMatch = true;
            }

            foreach ($result->matchedTags() as $tag) {
                $matchedTags[$tag] = true;
            }
        }

        $matchedTags = array_keys($matchedTags);

        return $this->expressionResults[] = new ExpressionResult(
            $node,
            $isMatch,
            $matchedTags,
            array_values(
                array_diff($this->tags, $matchedTags)
            )
        );
    }

    /**
     * Visit a LogicalNot node.
     *
     * @internal
     *
     * @param LogicalNot $node The node to visit.
     *
     * @return mixed
     */
    public function visitLogicalNot(LogicalNot $node)
    {
        $childResult = $node->child()->accept($this);

        return $this->expressionResults[] = new ExpressionResult(
            $node,
            !$childResult->isMatch(),
            $childResult->unmatchedTags(),
            $childResult->matchedTags()
        );
    }

    /**
     * Visit a Tag node.
     *
     * @internal
     *
     * @param Tag $node The node to visit.
     *
     * @return mixed
     */
    public function visitTag(Tag $node)
    {
        if ($this->caseSensitive) {
            $predicate = function ($tag) use ($node) {
                return $node->name() === $tag;
            };
        } else {
            $predicate = function ($tag) use ($node) {
                return 0 === strcasecmp($node->name(), $tag);
            };
        }

        return $this->matchTags(
            $node,
            $predicate
        );
    }

    /**
     * Visit a pattern node.
     *
     * @internal
     *
     * @param Pattern $node The node to visit.
     *
     * @return mixed
     */
    public function visitPattern(Pattern $node)
    {
        $pattern = '/^';

        foreach ($node->children() as $n) {
            $pattern .= $n->accept($this);
        }

        $pattern .= '$/';

        if (!$this->caseSensitive) {
            $pattern .= 'i';
        }

        return $this->matchTags(
            $node,
            function ($tag) use ($pattern) {
                return preg_match($pattern, $tag);
            }
        );
    }

    /**
     * Visit a PatternLiteral node.
     *
     * @param PatternLiteral $node The node to visit.
     *
     * @return mixed
     */
    public function visitPatternLiteral(PatternLiteral $node)
    {
        return preg_quote($node->string(), '/');
    }

    /**
     * Visit a PatternWildcard node.
     *
     * @param PatternWildcard $node The node to visit.
     *
     * @return mixed
     */
    public function visitPatternWildcard(PatternWildcard $node)
    {
        return '.*';
    }

    /**
     * Visit a EmptyExpression node.
     *
     * @internal
     *
     * @param EmptyExpression $node The node to visit.
     *
     * @return mixed
     */
    public function visitEmptyExpression(EmptyExpression $node)
    {
        return $this->expressionResults[] = new ExpressionResult(
            $node,
            $this->emptyIsWildcard,
            $this->emptyIsWildcard ? $this->tags : [],
            $this->emptyIsWildcard ? [] : $this->tags
        );
    }

    private function matchTags(ExpressionInterface $expression, $predicate)
    {
        $matchedTags = [];
        $unmatchedTags = [];

        foreach ($this->tags as $tag) {
            if ($predicate($tag)) {
                $matchedTags[] = $tag;
            } else {
                $unmatchedTags[] = $tag;
            }
        }

        return $this->expressionResults[] = new ExpressionResult(
            $expression,
            count($matchedTags) > 0,
            $matchedTags,
            $unmatchedTags
        );
    }

    private $caseSensitive;
    private $emptyIsWildcard;
    private $tags;
    private $expressionResults;
}
