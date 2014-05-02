<?php
namespace Icecave\Dialekt\Expression;

interface VisitorInterface
{
    public function visitLogicalAnd(LogicalAnd $expression);

    public function visitLogicalOr(LogicalOr $expression);

    public function visitLogicalNot(LogicalNot $expression);

    public function visitTag(Tag $expression);

    public function visitWildcard(Wildcard $expression);

    public function visitEmptyExpression(EmptyExpression $expression);

}
