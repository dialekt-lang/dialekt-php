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
                    new Token(Token::STRING, 'foo-bar'),
                ),
            ),
            'simple string with leading hyphen' => array(
                '-foo',
                array(
                    new Token(Token::STRING, '-foo'),
                ),
            ),
            'simple string with leading hyphen and asterisk' => array(
                '-foo*-',
                array(
                    new Token(Token::STRING, '-foo*-'),
                ),
            ),
            'multiple simple strings' => array(
                'foo bar',
                array(
                    new Token(Token::STRING, 'foo'),
                    new Token(Token::STRING, 'bar'),
                ),
            ),
            'quoted string' => array(
                '"foo bar"',
                array(
                    new Token(Token::STRING, 'foo bar'),
                ),
            ),
            'quoted string with escaped quote' => array(
                '"foo \"the\" bar"',
                array(
                    new Token(Token::STRING, 'foo "the" bar'),
                ),
            ),
            'quoted string with escaped quote' => array(
                '"foo \"the\" bar"',
                array(
                    new Token(Token::STRING, 'foo "the" bar'),
                ),
            ),
            'quoted string with escaped backslash' => array(
                '"foo\\\\bar"',
                array(
                    new Token(Token::STRING, 'foo\\bar'),
                ),
            ),
            'logical and' => array(
                'and',
                array(
                    new Token(Token::LOGICAL_AND, 'and'),
                ),
            ),
            'logical or' => array(
                'or',
                array(
                    new Token(Token::LOGICAL_OR, 'or'),
                ),
            ),
            'logical not' => array(
                'not',
                array(
                    new Token(Token::LOGICAL_NOT, 'not'),
                ),
            ),
            'logical operator case insensitivity' => array(
                'aNd Or NoT',
                array(
                    new Token(Token::LOGICAL_AND, 'aNd'),
                    new Token(Token::LOGICAL_OR, 'Or'),
                    new Token(Token::LOGICAL_NOT, 'NoT'),
                ),
            ),
            'open nesting' => array(
                '(',
                array(
                    new Token(Token::OPEN_BRACKET, '('),
                ),
            ),
            'close nesting' => array(
                ')',
                array(
                    new Token(Token::CLOSE_BRACKET, ')'),
                ),
            ),
            'nesting interrupts simple string' => array(
                'foo(bar)spam',
                array(
                    new Token(Token::STRING, 'foo'),
                    new Token(Token::OPEN_BRACKET, '('),
                    new Token(Token::STRING, 'bar'),
                    new Token(Token::CLOSE_BRACKET, ')'),
                    new Token(Token::STRING, 'spam'),
                ),
            ),
            'nesting interrupts simple string into quoted string' => array(
                'foo(bar)"spam"',
                array(
                    new Token(Token::STRING, 'foo'),
                    new Token(Token::OPEN_BRACKET, '('),
                    new Token(Token::STRING, 'bar'),
                    new Token(Token::CLOSE_BRACKET, ')'),
                    new Token(Token::STRING, 'spam'),
                ),
            ),
            'whitespace surrounding strings' => array(
                " \t\nfoo\tbar\nspam\t ",
                array(
                    new Token(Token::STRING, 'foo'),
                    new Token(Token::STRING, 'bar'),
                    new Token(Token::STRING, 'spam'),
                ),
            ),
        );
    }
}
