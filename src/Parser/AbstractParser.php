<?php
namespace Icecave\Dialekt\Parser;

use Icecave\Dialekt\AST\EmptyExpression;
use Icecave\Dialekt\AST\ExpressionInterface;
use Icecave\Dialekt\Parser\Exception\ParseException;

abstract class AbstractParser implements ParserInterface
{
    /**
     * @param LexerInterface|null $lexer The lexer used to tokenise input expressions.
     */
    public function __construct(LexerInterface $lexer = null)
    {
        if (null === $lexer) {
            $lexer = new Lexer;
        }

        $this->lexer = $lexer;
        $this->captureSourceOffsetStack = array();

        $this->setWildcardString(Token::WILDCARD_CHARACTER);
        $this->setCaptureSource(false);
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
     * Indicates whether or not the parser captures the expression source for
     * each AST node.
     *
     * @return boolean True if expression source is captured; otherwise, false.
     */
    public function captureSource()
    {
        return $this->captureSource;
    }

    /**
     * Set whether or not the parser captures the expression source for each AST
     * node.
     *
     * @param boolean $captureSource True if expression source is captured; otherwise, false.
     */
    public function setCaptureSource($captureSource)
    {
        $this->captureSource = $captureSource;
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
        if ($this->captureSource()) {
            $this->captureSourceExpression = $expression;
        }

        $this->tokens = $this->lexer->lex($expression);

        if (!$this->tokens) {
            $expression = new EmptyExpression;

            if ($this->captureSource()) {
                $expression->setSource($this->captureSourceExpression, 0);
            }

            return $expression;
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
     *
     * If source-capture is enabled, the current source code offset is recoreded.
     */
    protected function startExpression()
    {
        if ($this->captureSource()) {
            $this->captureSourceOffsetStack[] = current($this->tokens)->offset;
        }
    }

    /**
     * Record the end of an expression.
     *
     * If source-capture is enabled, the source code that produced this
     * expression is set on the expression object.
     *
     * @return ExpressionInterface
     */
    protected function endExpression(ExpressionInterface $expression)
    {
        if ($this->captureSource()) {

            // The start index has already been recoreded ...
            $startOffset = array_pop($this->captureSourceOffsetStack);

            // Find the end offset of the source for this node ...
            $index = key($this->tokens);

            // We're at the end of the input stream, so get the last token in
            // the token stream ...
            if (null === $index) {
                $index = count($this->tokens);
            }

            // The *current* token is the start of the next node, so we need to
            // look at the *previous* token.
            $token = $this->tokens[$index - 1];

            // Get the portion of the input string that corresponds to this node ...
            $source = substr(
                $this->captureSourceExpression,
                $startOffset,
                $token->offset + $token->length - $startOffset
            );

            $expression->setSource($source, $startOffset);
        }

        return $expression;
    }

    private $lexer;
    private $wildcardString;
    private $captureSource;
    private $captureSourceOffsetStack;
    private $captureSourceExpression;

    protected $tokens;
}
