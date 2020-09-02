<?php

namespace Dialekt\Parser;

use Dialekt\Parser\Exception\ParseException;

class Lexer implements LexerInterface
{
    /**
     * Tokenize an expression.
     *
     * @param string $expression The expression to tokenize.
     *
     * @return array<Token>   The tokens of the expression.
     * @throws ParseException if the expression is invalid.
     */
    public function lex($expression)
    {
        $this->currentOffset = 0;
        $this->currentLine = 1;
        $this->currentColumn = 0;
        $this->state = self::STATE_BEGIN;
        $this->tokens = [];
        $this->buffer = '';

        $length = strlen($expression);
        $previousChar = null;

        while ($this->currentOffset < $length) {
            $char = $expression[$this->currentOffset];
            ++$this->currentColumn;

            if (
                "\n" === $previousChar ||
                ("\r" === $previousChar && "\n" !== $char)
            ) {
                ++$this->currentLine;
                $this->currentColumn = 1;
            }

            if (self::STATE_SIMPLE_STRING === $this->state) {
                $this->handleSimpleStringState($char);
            } elseif (self::STATE_QUOTED_STRING === $this->state) {
                $this->handleQuotedStringState($char);
            } elseif (self::STATE_QUOTED_STRING_ESCAPE === $this->state) {
                $this->handleQuotedStringEscapeState($char);
            } else {
                $this->handleBeginState($char);
            }

            ++$this->currentOffset;
            $previousChar = $char;
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
            // ignore ...
        } elseif ($char === '(') {
            $this->startToken(Token::OPEN_BRACKET);
            $this->endToken($char);
        } elseif ($char === ')') {
            $this->startToken(Token::CLOSE_BRACKET);
            $this->endToken($char);
        } elseif ($char === '"') {
            $this->startToken(Token::STRING);
            $this->state = self::STATE_QUOTED_STRING;
        } else {
            $this->startToken(Token::STRING);
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
            $this->startToken(Token::OPEN_BRACKET);
            $this->endToken($char);
        } elseif ($char === ')') {
            $this->finalizeSimpleString();
            $this->startToken(Token::CLOSE_BRACKET);
            $this->endToken($char);
        } else {
            $this->buffer .= $char;
        }
    }

    private function handleQuotedStringState($char)
    {
        if ($char === '\\') {
            $this->state = self::STATE_QUOTED_STRING_ESCAPE;
        } elseif ($char === '"') {
            $this->endToken($this->buffer);
            $this->state = self::STATE_BEGIN;
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
            $this->nextToken->type = Token::LOGICAL_AND;
        } elseif (strcasecmp('or', $this->buffer) === 0) {
            $this->nextToken->type = Token::LOGICAL_OR;
        } elseif (strcasecmp('not', $this->buffer) === 0) {
            $this->nextToken->type = Token::LOGICAL_NOT;
        }

        $this->endToken($this->buffer, -1);
        $this->state = self::STATE_BEGIN;
        $this->buffer = '';
    }

    private function startToken($type)
    {
        $this->nextToken = new Token(
            $type,
            '',
            $this->currentOffset,
            0,
            $this->currentLine,
            $this->currentColumn
        );
    }

    private function endToken($value, $lengthAdjustment = 0)
    {
        $this->nextToken->value = $value;
        $this->nextToken->endOffset = $this->currentOffset
                                    + $lengthAdjustment
                                    + 1;
        $this->tokens[] = $this->nextToken;
        $this->nextToken = null;
    }

    const STATE_BEGIN                = 1;
    const STATE_SIMPLE_STRING        = 2;
    const STATE_QUOTED_STRING        = 3;
    const STATE_QUOTED_STRING_ESCAPE = 4;

    private $currentOffset;
    private $currentLine;
    private $currentColumn;
    private $state;
    private $tokens;
    private $nextToken;
    private $buffer;
}
