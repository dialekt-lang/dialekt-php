<?php
namespace Icecave\Dialekt\Parser;

use Icecave\Dialekt\AST\ExpressionInterface;
use Icecave\Dialekt\Parser\Exception\ParseException;

interface ParserInterface
{
    /**
     * Parse an expression.
     *
     * @param string $expression The expression to parse.
     *
     * @return ExpressionInterface The parsed expression.
     * @throws ParseException      if the expression is invalid.
     */
    public function parse($expression);
}
