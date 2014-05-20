<?php
namespace Icecave\Dialekt\Evaluator;

use Icecave\Dialekt\AST\EmptyExpression;
use Icecave\Dialekt\AST\ExpressionInterface;
use Icecave\Dialekt\AST\LogicalAnd;
use Icecave\Dialekt\AST\LogicalNot;
use Icecave\Dialekt\AST\LogicalOr;
use Icecave\Dialekt\AST\Pattern;
use Icecave\Dialekt\AST\PatternLiteral;
use Icecave\Dialekt\AST\PatternWildcard;
use Icecave\Dialekt\AST\Tag;
use Icecave\Dialekt\AST\VisitorInterface;

class Evaluator implements EvaluatorInterface, VisitorInterface
{
    /**
     * @param boolean $caseSensitive   True if tag matching should be case-sensitive; otherwise, false.
     * @param boolean $emptyIsWildcard True if an empty expression matches all tags; or false to match none.
     */
    public function __construct($caseSensitive = false, $emptyIsWildcard = false)
    {
        $this->caseSensitive = $caseSensitive;
        $this->emptyIsWildcard = $emptyIsWildcard;
    }

    /**
     * Evaluate an expression against a single tag.
     *
     * @param ExpressionInterface $expression The expression to evaluate.
     * @param string              $tag        The tag to evaluate against.
     *
     * @return boolean True if the expression matches the given tag; otherwise, false.
     */
    public function evaluate(ExpressionInterface $expression, $tags)
    {
        $this->tags = $tags;
        $result = $expression->accept($this);
        $this->tags = null;

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
        foreach ($node->children() as $n) {
            if (!$n->accept($this)) {
                return false;
            }
        }

        return true;
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
        foreach ($node->children() as $n) {
            if ($n->accept($this)) {
                return true;
            }
        }

        return false;
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
        return !$node->child()->accept($this);
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
            return in_array(
                $node->name(),
                $this->tags
            );
        }

        foreach ($this->tags as $tag) {
            if (0 === strcasecmp($node->name(), $tag)) {
                return true;
            }
        }

        return false;
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

        foreach ($this->tags as $tag) {
            if (preg_match($pattern, $tag)) {
                return true;
            }
        }

        return false;
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
        return $this->emptyIsWildcard;
    }

    private $tags;
    private $caseSensitive;
    private $emptyIsWildcard;
}
