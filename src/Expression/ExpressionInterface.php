<?php
namespace Icecave\Dialekt\Expression;

/**
 * Base interface for expressions.
 */
interface ExpressionInterface
{
    public function accept(VisitorInterface $visitor);
}
