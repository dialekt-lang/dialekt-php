<?php
namespace Icecave\Dialekt\Parser;

use PHPUnit_Framework_TestCase;

class DefaultLexerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->lexer = new DefaultLexer;
    }

    /**
     * @dataProvider lexTestVectors
     */
    public function testLex($expression, $expectedTokens)
    {
        $tokens = $this->lexer->lex($expression);

        $this->assertEquals($expectedTokens, $tokens);
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
            'simple string' => array(
                'foo-bar',
                array(
                    new Token(Token::TOKEN_STRING, 'foo-bar'),
                ),
            ),
            'multiple simple strings' => array(
                'foo bar',
                array(
                    new Token(Token::TOKEN_STRING, 'foo'),
                    new Token(Token::TOKEN_STRING, 'bar'),
                ),
            ),
            'quoted string' => array(
                '"foo bar"',
                array(
                    new Token(Token::TOKEN_STRING, 'foo bar'),
                ),
            ),
            'quoted string with escaped quote' => array(
                '"foo \"the\" bar"',
                array(
                    new Token(Token::TOKEN_STRING, 'foo "the" bar'),
                ),
            ),
            'quoted string with escaped quote' => array(
                '"foo \"the\" bar"',
                array(
                    new Token(Token::TOKEN_STRING, 'foo "the" bar'),
                ),
            ),
            'quoted string with escaped backslash' => array(
                '"foo\\\\bar"',
                array(
                    new Token(Token::TOKEN_STRING, 'foo\\bar'),
                ),
            ),
            'logical and' => array(
                'and',
                array(
                    new Token(Token::TOKEN_LOGICAL_AND, 'and'),
                ),
            ),
            'logical or' => array(
                'or',
                array(
                    new Token(Token::TOKEN_LOGICAL_OR, 'or'),
                ),
            ),
            'logical not' => array(
                'not',
                array(
                    new Token(Token::TOKEN_LOGICAL_NOT, 'not'),
                ),
            ),
            'logical operator case insensitivity' => array(
                'aNd Or NoT',
                array(
                    new Token(Token::TOKEN_LOGICAL_AND, 'aNd'),
                    new Token(Token::TOKEN_LOGICAL_OR, 'Or'),
                    new Token(Token::TOKEN_LOGICAL_NOT, 'NoT'),
                ),
            ),
            'open nesting' => array(
                '(',
                array(
                    new Token(Token::TOKEN_OPEN_NEST, '('),
                ),
            ),
            'close nesting' => array(
                ')',
                array(
                    new Token(Token::TOKEN_CLOSE_NEST, ')'),
                ),
            ),
            'nesting interrupts simple string' => array(
                'foo(bar)spam',
                array(
                    new Token(Token::TOKEN_STRING, 'foo'),
                    new Token(Token::TOKEN_OPEN_NEST, '('),
                    new Token(Token::TOKEN_STRING, 'bar'),
                    new Token(Token::TOKEN_CLOSE_NEST, ')'),
                    new Token(Token::TOKEN_STRING, 'spam'),
                ),
            ),
            'whitespace' => array(
                " \t\nfoo\tbar\nspam\t ",
                array(
                    new Token(Token::TOKEN_STRING, 'foo'),
                    new Token(Token::TOKEN_STRING, 'bar'),
                    new Token(Token::TOKEN_STRING, 'spam'),
                ),
            ),
        );
    }
}
