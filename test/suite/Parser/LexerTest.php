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
                    new Token(Token::STRING, 'foo-bar'),
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
                    new Token(Token::OPEN_NEST, '('),
                ),
            ),
            'close nesting' => array(
                ')',
                array(
                    new Token(Token::CLOSE_NEST, ')'),
                ),
            ),
            'nesting interrupts simple string' => array(
                'foo(bar)spam',
                array(
                    new Token(Token::STRING, 'foo'),
                    new Token(Token::OPEN_NEST, '('),
                    new Token(Token::STRING, 'bar'),
                    new Token(Token::CLOSE_NEST, ')'),
                    new Token(Token::STRING, 'spam'),
                ),
            ),
            'nesting interrupts simple string into quoted string' => array(
                'foo(bar)"spam"',
                array(
                    new Token(Token::STRING, 'foo'),
                    new Token(Token::OPEN_NEST, '('),
                    new Token(Token::STRING, 'bar'),
                    new Token(Token::CLOSE_NEST, ')'),
                    new Token(Token::STRING, 'spam'),
                ),
            ),
            'whitespace' => array(
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
