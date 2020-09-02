<?php

namespace Dialekt\AST;

/**
 * Interface for node visitors.
 */
interface VisitorInterface
{
    /**
     * Visit a LogicalAnd node.
     *
     * @param LogicalAnd $node The node to visit.
     *
     * @return mixed
     */
    public function visitLogicalAnd(LogicalAnd $node);

    /**
     * Visit a LogicalOr node.
     *
     * @param LogicalOr $node The node to visit.
     *
     * @return mixed
     */
    public function visitLogicalOr(LogicalOr $node);

    /**
     * Visit a LogicalNot node.
     *
     * @param LogicalNot $node The node to visit.
     *
     * @return mixed
     */
    public function visitLogicalNot(LogicalNot $node);

    /**
     * Visit a Tag node.
     *
     * @param Tag $node The node to visit.
     *
     * @return mixed
     */
    public function visitTag(Tag $node);

    /**
     * Visit a pattern node.
     *
     * @param Pattern $node The node to visit.
     *
     * @return mixed
     */
    public function visitPattern(Pattern $node);

    /**
     * Visit a PatternLiteral node.
     *
     * @param PatternLiteral $node The node to visit.
     *
     * @return mixed
     */
    public function visitPatternLiteral(PatternLiteral $node);

    /**
     * Visit a PatternWildcard node.
     *
     * @param PatternWildcard $node The node to visit.
     *
     * @return mixed
     */
    public function visitPatternWildcard(PatternWildcard $node);

    /**
     * Visit a EmptyExpression node.
     *
     * @param EmptyExpression $node The node to visit.
     *
     * @return mixed
     */
    public function visitEmptyExpression(EmptyExpression $node);
}
