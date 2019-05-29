<?php
namespace Icecave\Dialekt\Parser;

use Icecave\Dialekt\AST\ExpressionInterface;
use Icecave\Dialekt\Parser\Exception\ParseException;

interface ParserInterface
{
    /**
     * Parse an expression.
     *
     * @param string         $expression The expression to parse.
     * @param LexerInterface $lexer      The lexer to use to tokenise the string, or null to use the default.
     *
     * @return ExpressionInterface The parsed expression.
     * @throws ParseException      if the expression is invalid.
     */
    public function parse($expression, LexerInterface $lexer = null);

    /**
     * Parse an expression that has already beed tokenized.
     *
     * @param array<Token> The array of tokens that form the expression.
     *
     * @return ExpressionInterface The parsed expression.
     * @throws ParseException      if the expression is invalid.
     */
    public function parseTokens(array $tokens);
}
