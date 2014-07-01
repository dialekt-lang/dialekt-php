<?php
namespace Icecave\Dialekt\Parser;

use Icecave\Dialekt\AST\EmptyExpression;
use Icecave\Dialekt\AST\ExpressionInterface;
use Icecave\Dialekt\Parser\Exception\ParseException;

abstract class AbstractParser implements ParserInterface
{
    public function __construct()
    {
        $this->tokenStack = array();

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
            $lexer = new Lexer;
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
        $this->tokens = $tokens;

        if (!$this->tokens) {
            return new EmptyExpression;
        }

        $expression = $this->parseExpression();

        if (null !== key($this->tokens)) {
            throw new ParseException(
                'Unexpected ' . Token::typeDescription(current($this->tokens)->type) . ', expected end of input.'
            );
        }

        return $expression;
    }

    abstract protected function parseExpression();

    protected function expectToken()
    {
        $types = func_get_args();
        $token = current($this->tokens);

        if (!$token) {
            throw new ParseException(
                'Unexpected end of input, expected ' . $this->formatExpectedTokenNames($types) . '.'
            );
        } elseif (!in_array($token->type, $types)) {
            throw new ParseException(
                'Unexpected ' . Token::typeDescription($token->type) . ', expected ' . $this->formatExpectedTokenNames($types) . '.'
            );
        }

        return $token;
    }

    protected function formatExpectedTokenNames(array $types)
    {
        $types = array_map(
            'Icecave\Dialekt\Parser\Token::typeDescription',
            $types
        );

        if (count($types) === 1) {
            return $types[0];
        }

        $lastType = array_pop($types);

        return implode(', ', $types) . ' or ' . $lastType;
    }

    /**
     * Record the start of an expression.
     */
    protected function startExpression()
    {
        $this->tokenStack[] = current($this->tokens);
    }

    /**
     * Record the end of an expression.
     *
     * @return ExpressionInterface
     */
    protected function endExpression(ExpressionInterface $expression)
    {
        // Find the end offset of the source for this node ...
        $index = key($this->tokens);

        // We're at the end of the input stream, so get the last token in
        // the token stream ...
        if (null === $index) {
            $index = count($this->tokens);
        }

        // The *current* token is the start of the next node, so we need to
        // look at the *previous* token to find the last token of this
        // expression ...
        $expression->setTokens(
            array_pop($this->tokenStack),
            $this->tokens[$index - 1]
        );

        return $expression;
    }

    private $wildcardString;
    private $tokenStack;

    protected $tokens;
}
