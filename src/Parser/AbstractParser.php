<?php

namespace Dialekt\Parser;

use Dialekt\AST\EmptyExpression;
use Dialekt\AST\ExpressionInterface;
use Dialekt\Parser\Exception\ParseException;

abstract class AbstractParser implements ParserInterface
{
    public function __construct()
    {
        $this->tokenStack = [];

        $this->setWildcardString(Token::WILDCARD_CHARACTER);
    }

    /**
     * Fetch the string to use as a wildcard placeholder.
     *
     * @return string The string to use as a wildcard placeholder.
     */
    public function wildcardString()
    {
        return $this->wildcardString;
    }

    /**
     * Set the string to use as a wildcard placeholder.
     *
     * @param string $wildcardString The string to use as a wildcard placeholder.
     */
    public function setWildcardString($wildcardString)
    {
        $this->wildcardString = $wildcardString;
    }

    /**
     * Parse an expression.
     *
     * @param string         $expression The expression to parse.
     * @param LexerInterface $lexer      The lexer to use to tokenise the string, or null to use the default.
     *
     * @return ExpressionInterface The parsed expression.
     * @throws ParseException      if the expression is invalid.
     */
    public function parse($expression, LexerInterface $lexer = null)
    {
        if (null === $lexer) {
            $lexer = new Lexer();
        }

        return $this->parseTokens(
            $lexer->lex($expression)
        );
    }

    /**
     * Parse an expression that has already beed tokenized.
     *
     * @param array<Token> The array of tokens that form the expression.
     *
     * @return ExpressionInterface The parsed expression.
     * @throws ParseException      if the expression is invalid.
     */
    public function parseTokens(array $tokens)
    {
        if (!$tokens) {
            return new EmptyExpression();
        }

        $this->tokens = $tokens;
        $this->currentToken = current($tokens);
        $this->previousToken = null;

        $expression = $this->parseExpression();

        if ($this->currentToken) {
            throw new ParseException(
                'Unexpected ' . Token::typeDescription($this->currentToken->type) . ', expected end of input.'
            );
        }

        return $expression;
    }

    abstract protected function parseExpression();

    protected function expectToken()
    {
        $types = func_get_args();

        if (!$this->currentToken) {
            throw new ParseException(
                'Unexpected end of input, expected ' . $this->formatExpectedTokenNames($types) . '.'
            );
        } elseif (!in_array($this->currentToken->type, $types)) {
            throw new ParseException(
                'Unexpected ' . Token::typeDescription($this->currentToken->type) . ', expected ' . $this->formatExpectedTokenNames($types) . '.'
            );
        }
    }

    protected function formatExpectedTokenNames(array $types)
    {
        $types = array_map(
            'Dialekt\Parser\Token::typeDescription',
            $types
        );

        if (count($types) === 1) {
            return $types[0];
        }

        $lastType = array_pop($types);

        return implode(', ', $types) . ' or ' . $lastType;
    }

    protected function nextToken()
    {
        $this->previousToken = $this->currentToken;

        next($this->tokens);

        $this->currentToken = current($this->tokens) ?: null;
    }

    /**
     * Record the start of an expression.
     */
    protected function startExpression()
    {
        $this->tokenStack[] = $this->currentToken;
    }

    /**
     * Record the end of an expression.
     *
     * @return ExpressionInterface
     */
    protected function endExpression(ExpressionInterface $expression)
    {
        $expression->setTokens(
            array_pop($this->tokenStack),
            $this->previousToken
        );
    }

    private $wildcardString;
    private $tokenStack;
    private $tokens;
    private $previousToken;

    protected $currentToken;
}
