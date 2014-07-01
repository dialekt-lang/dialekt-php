<?php
namespace Icecave\Dialekt\Parser;

use PHPUnit_Framework_TestCase;

class LexerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->lexer = new Lexer;
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
        $this->setExpectedException(
            'Icecave\Dialekt\Parser\Exception\ParseException',
            'Expected closing quote.'
        );

        $this->lexer->lex('"foo');
    }

    public function testLexFailureInQuotedStringEscape()
    {
        $this->setExpectedException(
            'Icecave\Dialekt\Parser\Exception\ParseException',
            'Expected character after backslash.'
        );

        $this->lexer->lex('"foo\\');
    }

    public function lexTestVectors()
    {
        return array(
            'empty expression' => array(
                '',
                array(),
            ),
            'whitespace only' => array(
                " \n \t ",
                array(),
            ),
            'simple string' => array(
                'foo-bar',
                array(
                    new Token(Token::STRING, 'foo-bar', 0, 7, 1, 1),
                ),
            ),
            'simple string with leading hyphen' => array(
                '-foo',
                array(
                    new Token(Token::STRING, '-foo', 0, 4, 1, 1),
                ),
            ),
            'simple string with leading hyphen and asterisk' => array(
                '-foo*-',
                array(
                    new Token(Token::STRING, '-foo*-', 0, 6, 1, 1),
                ),
            ),
            'multiple simple strings' => array(
                'foo bar',
                array(
                    new Token(Token::STRING, 'foo', 0, 3, 1, 1),
                    new Token(Token::STRING, 'bar', 4, 7, 1, 5),
                ),
            ),
            'quoted string' => array(
                '"foo bar"',
                array(
                    new Token(Token::STRING, 'foo bar', 0, 9, 1, 1),
                ),
            ),
            'quoted string with escaped quote' => array(
                '"foo \"the\" bar"',
                array(
                    new Token(Token::STRING, 'foo "the" bar', 0, 17, 1, 1),
                ),
            ),
            'quoted string with escaped backslash' => array(
                '"foo\\\\bar"',
                array(
                    new Token(Token::STRING, 'foo\\bar', 0, 10, 1, 1),
                ),
            ),
            'logical and' => array(
                'and',
                array(
                    new Token(Token::LOGICAL_AND, 'and', 0, 3, 1, 1),
                ),
            ),
            'logical or' => array(
                'or',
                array(
                    new Token(Token::LOGICAL_OR, 'or', 0, 2, 1, 1),
                ),
            ),
            'logical not' => array(
                'not',
                array(
                    new Token(Token::LOGICAL_NOT, 'not', 0, 3, 1, 1),
                ),
            ),
            'logical operator case insensitivity' => array(
                'aNd Or NoT',
                array(
                    new Token(Token::LOGICAL_AND, 'aNd', 0, 3,  1, 1),
                    new Token(Token::LOGICAL_OR,  'Or',  4, 6,  1, 5),
                    new Token(Token::LOGICAL_NOT, 'NoT', 7, 10, 1, 8),
                ),
            ),
            'open nesting' => array(
                '(',
                array(
                    new Token(Token::OPEN_BRACKET, '(', 0, 1, 1, 1),
                ),
            ),
            'close nesting' => array(
                ')',
                array(
                    new Token(Token::CLOSE_BRACKET, ')', 0, 1, 1, 1),
                ),
            ),
            'nesting interrupts simple string' => array(
                'foo(bar)spam',
                array(
                    new Token(Token::STRING,        'foo',  0, 3,  1, 1),
                    new Token(Token::OPEN_BRACKET,  '(',    3, 4,  1, 4),
                    new Token(Token::STRING,        'bar',  4, 7,  1, 5),
                    new Token(Token::CLOSE_BRACKET, ')',    7, 8,  1, 8),
                    new Token(Token::STRING,        'spam', 8, 12, 1, 9),
                ),
            ),
            'nesting interrupts simple string into quoted string' => array(
                'foo(bar)"spam"',
                array(
                    new Token(Token::STRING,        'foo',  0, 3,  1, 1),
                    new Token(Token::OPEN_BRACKET,  '(',    3, 4,  1, 4),
                    new Token(Token::STRING,        'bar',  4, 7,  1, 5),
                    new Token(Token::CLOSE_BRACKET, ')',    7, 8,  1, 8),
                    new Token(Token::STRING,        'spam', 8, 14, 1, 9),
                ),
            ),
            'whitespace surrounding strings' => array(
                " \t\nfoo\tbar\nspam\t ",
                array(
                    new Token(Token::STRING, 'foo',   3, 6,  2, 1),
                    new Token(Token::STRING, 'bar',   7, 10, 2, 5),
                    new Token(Token::STRING, 'spam', 11, 15, 3, 1),
                ),
            ),
            'newline handling' => array(
                '"foo'. "\n" . 'bar" baz',
                array(
                    new Token(Token::STRING, 'foo' . "\n" . 'bar',  0,  9, 1, 1),
                    new Token(Token::STRING, 'baz',                10, 13, 2, 6),
                ),
            ),
            'carriage return handling' => array(
                '"foo'. "\r" . 'bar" baz',
                array(
                    new Token(Token::STRING, 'foo' . "\r" . 'bar',  0,  9, 1, 1),
                    new Token(Token::STRING, 'baz',                10, 13, 2, 6),
                ),
            ),
            'carriage return + newline handling' => array(
                '"foo'. "\r\n" . 'bar" baz',
                array(
                    new Token(Token::STRING, 'foo' . "\r\n" . 'bar',  0, 10, 1, 1),
                    new Token(Token::STRING, 'baz',                  11, 14, 2, 6),
                ),
            ),
        );
    }
}
