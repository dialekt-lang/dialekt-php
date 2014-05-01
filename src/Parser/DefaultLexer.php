<?php
namespace Icecave\Dialekt\Parser;

use Icecave\Dialekt\Parser\Exception\ParseException;

class DefaultLexer implements LexerInterface
{
    /**
     * Tokenize an expression.
     *
     * @param string $expression The expression to parse.
     *
     * @return array<Token>   The tokens of the expression.
     * @throws ParseException if the expression is invalid.
     */
    public function lex($expression)
    {
        $this->state = self::STATE_START;
        $this->tokens = array();
        $this->buffer = '';

        $length = strlen($expression);

        for ($index = 0; $index < $length; ++$index) {
            $char = $expression[$index];

            if (self::STATE_SIMPLE_STRING === $this->state) {
                $this->handleSimpleStringState($char);
            } elseif (self::STATE_QUOTED_STRING === $this->state) {
                $this->handleQuotedStringState($char);
            } elseif (self::STATE_QUOTED_STRING_ESCAPE === $this->state) {
                $this->handleQuotedStringEscapeState($char);
            } else {
                $this->handleBeginState($char);
            }
        }

        if (self::STATE_SIMPLE_STRING === $this->state) {
            $this->finalizeSimpleString();
        } elseif (self::STATE_QUOTED_STRING === $this->state) {
            throw new ParseException('Expected closing quote.');
        } elseif (self::STATE_QUOTED_STRING_ESCAPE === $this->state) {
            throw new ParseException('Expected character after backslash.');
        }

        return $this->tokens;
    }

    private function handleBeginState($char)
    {
        if (ctype_space($char)) {
            // ignore
        } elseif ($char === '(') {
            $this->tokens[] = new Token(Token::TOKEN_OPEN_NEST, $char);
        } elseif ($char === ')') {
            $this->tokens[] = new Token(Token::TOKEN_CLOSE_NEST, $char);
        } elseif ($char === '"') {
            $this->state = self::STATE_QUOTED_STRING;
        } elseif (ctype_alnum($char)) {
            $this->state = self::STATE_SIMPLE_STRING;
            $this->buffer = $char;
        }
    }

    private function handleSimpleStringState($char)
    {
        if (ctype_space($char)) {
            $this->finalizeSimpleString();
        } elseif ($char === '(') {
            $this->finalizeSimpleString();
            $this->tokens[] = new Token(Token::TOKEN_OPEN_NEST, $char);
        } elseif ($char === ')') {
            $this->finalizeSimpleString();
            $this->tokens[] = new Token(Token::TOKEN_CLOSE_NEST, $char);
        } else {
            $this->buffer .= $char;
        }
    }

    private function handleQuotedStringState($char)
    {
        if ($char === '\\') {
            $this->state = self::STATE_QUOTED_STRING_ESCAPE;
        } elseif ($char === '"') {
            $this->tokens[] = new Token(Token::TOKEN_STRING, $this->buffer);
            $this->state = self::STATE_START;
            $this->buffer = '';
        } else {
            $this->buffer .= $char;
        }
    }

    private function handleQuotedStringEscapeState($char)
    {
        $this->state = self::STATE_QUOTED_STRING;
        $this->buffer .= $char;
    }

    private function finalizeSimpleString()
    {
        if (strcasecmp('and', $this->buffer) === 0) {
            $tokenType = Token::TOKEN_LOGICAL_AND;
        } elseif (strcasecmp('or', $this->buffer) === 0) {
            $tokenType = Token::TOKEN_LOGICAL_OR;
        } elseif (strcasecmp('not', $this->buffer) === 0) {
            $tokenType = Token::TOKEN_LOGICAL_NOT;
        } else {
            $tokenType = Token::TOKEN_STRING;
        }

        $this->tokens[] = new Token($tokenType, $this->buffer);
        $this->state = self::STATE_START;
        $this->buffer = '';
    }

    const STATE_START                = 1;
    const STATE_SIMPLE_STRING        = 2;
    const STATE_QUOTED_STRING        = 3;
    const STATE_QUOTED_STRING_ESCAPE = 4;

    private $state;
    private $tokens;
    private $buffer;
}
