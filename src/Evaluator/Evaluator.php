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

/**
 * Match parsed expressions against string tags.
 */
class Evaluator implements VisitorInterface
{
    /**
     * @param boolean $caseSensitive                    True if tag matching should be case-sensitive; otherwise, false.
     * @param boolean $emptyExpressionMatchesEverything True if an empty expression matches all tags; or false to match none.
     */
    public function __construct(
        $caseSensitive = false,
        $emptyExpressionMatchesEverything = false
    ) {
        $this->caseSensitive = $caseSensitive;
        $this->emptyExpressionMatchesEverything = $emptyExpressionMatchesEverything;
    }

    /**
     * Check if an expression evaluates to true for a single tag.
     *
     * @param ExpressionInterface $expression The expression to evaluate.
     * @param string              $tag        The tag to match.
     *
     * @return boolean True if the expression matches the given tag; otherwise, false.
     */
    public function match(ExpressionInterface $expression, $tag)
    {
        $this->currentTag = $tag;
        $result = $expression->accept($this);
        $this->currentTag = null;

        return $result;
    }

    /**
     * Check if an expression evaluates to true for all of the given tags.
     *
     * @param ExpressionInterface $expression The expression to evaluate.
     * @param mixed<string>       $tags       The tags to match.
     *
     * @return boolean True if the expression matches all of the given tags; otherwise, false.
     */
    public function matchAll(ExpressionInterface $expression, $tags)
    {
        foreach ($tags as $tag) {
            if (!$this->match($expression, $tag)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if an expression evaluates to true for any of the given tags.
     *
     * @param ExpressionInterface $expression The expression to evaluate.
     * @param mixed<string>       $tags       The tags to match.
     *
     * @return boolean True if the expression matches any of the given tags; otherwise, false.
     */
    public function matchAny(ExpressionInterface $expression, $tags)
    {
        foreach ($tags as $tag) {
            if ($this->match($expression, $tag)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Partition a traversible set of tags into two arrays of matching and
     * non-matching tags based on an expression.
     *
     * @param ExpressionInterface $expression The expression used to partition the array.
     * @param mixed<string>       $tags       The array of tags to partition.
     *
     * @return tuple<array,array> A 2-tuple containing arrays of the matched and unmatched tags.
     */
    public function partition(ExpressionInterface $expression, $tags)
    {
        $matched = array();
        $notMatched = array();

        foreach ($tags as $tag) {
            if ($this->match($expression, $tag)) {
                $matched[] = $tag;
            } else {
                $notMatched[] = $tag;
            }
        }

        return array(
            $matched,
            $notMatched,
        );
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
            return $this->currentTag === $node->name();
        }

        return 0 === strcasecmp($this->currentTag, $node->name());
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

        return 0 !== preg_match($pattern, $this->currentTag);
    }

    /**
     * Visit a PatternLiteral node.
     *
     * @internal
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
     * @internal
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
        return $this->emptyExpressionMatchesEverything;
    }

    private $currentTag;
    private $emptyExpressionMatchesEverything;
}
