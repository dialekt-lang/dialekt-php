<?php

namespace Dialekt\Parser;

use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    public function setUp(): void
    {
        $this->lexer = new Lexer();
    }

    /**
     * @dataProvider lexTestVectors
     */
    public function testLex($expression, $expectedResult)
    {
        $result = $this->lexer->lex($expression);

        $this->assertEquals($expectedResult, $result);
    }

    public function testLexFailureInQuotedString()
    {
        $this->expectException(
            'Dialekt\Parser\Exception\ParseException',
            'Expected closing quote.'
        );

        $this->lexer->lex('"foo');
    }

    public function testLexFailureInQuotedStringEscape()
    {
        $this->expectException(
            'Dialekt\Parser\Exception\ParseException',
            'Expected character after backslash.'
        );

        $this->lexer->lex('"foo\\');
    }

    public function lexTestVectors()
    {
        return [
            'empty expression' => [
                '',
                [],
            ],
            'whitespace only' => [
                " \n \t ",
                [],
            ],
            'simple string' => [
                'foo-bar',
                [
                    new Token(Token::STRING, 'foo-bar', 0, 7, 1, 1),
                ],
            ],
            'simple string with leading hyphen' => [
                '-foo',
                [
                    new Token(Token::STRING, '-foo', 0, 4, 1, 1),
                ],
            ],
            'simple string with leading hyphen and asterisk' => [
                '-foo*-',
                [
                    new Token(Token::STRING, '-foo*-', 0, 6, 1, 1),
                ],
            ],
            'multiple simple strings' => [
                'foo bar',
                [
                    new Token(Token::STRING, 'foo', 0, 3, 1, 1),
                    new Token(Token::STRING, 'bar', 4, 7, 1, 5),
                ],
            ],
            'quoted string' => [
                '"foo bar"',
                [
                    new Token(Token::STRING, 'foo bar', 0, 9, 1, 1),
                ],
            ],
            'quoted string with escaped quote' => [
                '"foo \"the\" bar"',
                [
                    new Token(Token::STRING, 'foo "the" bar', 0, 17, 1, 1),
                ],
            ],
            'quoted string with escaped backslash' => [
                '"foo\\\\bar"',
                [
                    new Token(Token::STRING, 'foo\\bar', 0, 10, 1, 1),
                ],
            ],
            'logical and' => [
                'and',
                [
                    new Token(Token::LOGICAL_AND, 'and', 0, 3, 1, 1),
                ],
            ],
            'logical or' => [
                'or',
                [
                    new Token(Token::LOGICAL_OR, 'or', 0, 2, 1, 1),
                ],
            ],
            'logical not' => [
                'not',
                [
                    new Token(Token::LOGICAL_NOT, 'not', 0, 3, 1, 1),
                ],
            ],
            'logical operator case insensitivity' => [
                'aNd Or NoT',
                [
                    new Token(Token::LOGICAL_AND, 'aNd', 0, 3, 1, 1),
                    new Token(Token::LOGICAL_OR, 'Or', 4, 6, 1, 5),
                    new Token(Token::LOGICAL_NOT, 'NoT', 7, 10, 1, 8),
                ],
            ],
            'open nesting' => [
                '(',
                [
                    new Token(Token::OPEN_BRACKET, '(', 0, 1, 1, 1),
                ],
            ],
            'close nesting' => [
                ')',
                [
                    new Token(Token::CLOSE_BRACKET, ')', 0, 1, 1, 1),
                ],
            ],
            'nesting interrupts simple string' => [
                'foo(bar)spam',
                [
                    new Token(Token::STRING, 'foo', 0, 3, 1, 1),
                    new Token(Token::OPEN_BRACKET, '(', 3, 4, 1, 4),
                    new Token(Token::STRING, 'bar', 4, 7, 1, 5),
                    new Token(Token::CLOSE_BRACKET, ')', 7, 8, 1, 8),
                    new Token(Token::STRING, 'spam', 8, 12, 1, 9),
                ],
            ],
            'nesting interrupts simple string into quoted string' => [
                'foo(bar)"spam"',
                [
                    new Token(Token::STRING, 'foo', 0, 3, 1, 1),
                    new Token(Token::OPEN_BRACKET, '(', 3, 4, 1, 4),
                    new Token(Token::STRING, 'bar', 4, 7, 1, 5),
                    new Token(Token::CLOSE_BRACKET, ')', 7, 8, 1, 8),
                    new Token(Token::STRING, 'spam', 8, 14, 1, 9),
                ],
            ],
            'whitespace surrounding strings' => [
                " \t\nfoo\tbar\nspam\t ",
                [
                    new Token(Token::STRING, 'foo', 3, 6, 2, 1),
                    new Token(Token::STRING, 'bar', 7, 10, 2, 5),
                    new Token(Token::STRING, 'spam', 11, 15, 3, 1),
                ],
            ],
            'newline handling' => [
                '"foo' . "\n" . 'bar" baz',
                [
                    new Token(Token::STRING, 'foo' . "\n" . 'bar', 0, 9, 1, 1),
                    new Token(Token::STRING, 'baz', 10, 13, 2, 6),
                ],
            ],
            'carriage return handling' => [
                '"foo' . "\r" . 'bar" baz',
                [
                    new Token(Token::STRING, 'foo' . "\r" . 'bar', 0, 9, 1, 1),
                    new Token(Token::STRING, 'baz', 10, 13, 2, 6),
                ],
            ],
            'carriage return + newline handling' => [
                '"foo' . "\r\n" . 'bar" baz',
                [
                    new Token(Token::STRING, 'foo' . "\r\n" . 'bar', 0, 10, 1, 1),
                    new Token(Token::STRING, 'baz', 11, 14, 2, 6),
                ],
            ],
        ];
    }
}
