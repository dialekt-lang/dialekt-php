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

        foreach (str_split($expression) as $char) {
            $this->process($char);
        }

        $this->finalize();

        return $this->tokens;
    }

    private function process($char)
    {
        switch ($this->state) {
            case self::STATE_SIMPLE_STRING:
                return $this->processSimpleString($char);
            case self::STATE_QUOTED_STRING:
                return $this->processQuotedString($char);
            case self::STATE_QUOTED_STRING_ESCAPE:
                return $this->processQuotedStringEscape($char);
        }

        return $this->processStart($char);
    }

    private function finalize()
    {
        switch ($this->state) {
            case self::STATE_SIMPLE_STRING:
                return $this->finalizeSimpleString();
            case self::STATE_QUOTED_STRING:
                throw new ParseException('Expected closing quote.');
            case self::STATE_QUOTED_STRING_ESCAPE:
                throw new ParseException('Expected character after backslash.');
        }
    }

    private function processStart($char)
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

    private function processSimpleString($char)
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

    private function processQuotedString($char)
    {
        if ($char === '\\') {
            $this->state = self::STATE_QUOTED_STRING_ESCAPE;
        } elseif ($char === '"') {
            $this->emit(Token::TOKEN_STRING);
        } else {
            $this->buffer .= $char;
        }
    }

    private function processQuotedStringEscape($char)
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

        $this->emit($tokenType);
    }

    private function emit($type)
    {
        $this->state = self::STATE_START;
        $this->tokens[] = new Token($type, $this->buffer);
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
