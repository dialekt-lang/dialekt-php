<?php
namespace Icecave\Dialekt\Parser;

use Icecave\Dialekt\Expression\ExpressionInterface;
use Icecave\Dialekt\Parser\Exception\ParseException;

class Parser implements ParserInterface
{
    public function __construct(LexerInterface $lexer = null)
    {
        if (null === $lexer) {
            $lexer = new Lexer;
        }

        $this->lexer = $lexer;
    }

    /**
     * Parse an expression.
     *
     * @param string $expression The expression to parse.
     *
     * @return ExpressionInterface The parsed expression.
     * @throws ParseException      if the expression is invalid.
     */
    public function parse($expression)
    {
        $tokens = $this->lexer->lex($expression);

        foreach ($tokens as $token) {

        }
    }

    private $lexer;
}
