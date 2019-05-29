<?php
namespace Dialekt\Parser;

use Dialekt\Parser\Exception\ParseException;

interface LexerInterface
{
    /**
     * Tokenize an expression.
     *
     * @param string $expression The expression to tokenize.
     *
     * @return array<Token>   The tokens of the expression.
     * @throws ParseException if the expression is invalid.
     */
    public function lex($expression);
}
