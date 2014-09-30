<?php
namespace Icecave\Dialekt\Parser;

use Icecave\Dialekt\AST\EmptyExpression;
use Icecave\Dialekt\AST\LogicalAnd;
use Icecave\Dialekt\AST\Tag;
use Icecave\Dialekt\Parser\Exception\ParseException;
use Icecave\Dialekt\Renderer\ExpressionRenderer;
use PHPUnit_Framework_TestCase;

/**
 * @covers Icecave\Dialekt\Parser\ListParser
 * @covers Icecave\Dialekt\Parser\AbstractParser
 */
class ListParserTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->renderer = new ExpressionRenderer();
        $this->parser = new ListParser();
    }

    /**
     * @dataProvider parseTestVectors
     */
    public function testParse($expression, $expectedResult, array $expectedTags)
    {
        $result = $this->parser->parse($expression);

        $this->assertEquals(
            $this->renderer->render($expectedResult),
            $this->renderer->render($result)
        );
    }

    /**
     * @dataProvider parseTestVectors
     */
    public function testParseAsArray($expression, $expectedResult, array $expectedTags)
    {
        $result = $this->parser->parseAsArray($expression);

        $this->assertSame($expectedTags, $result);
    }

    public function testParseFailureWithNonString()
    {
        $this->setExpectedException(
            'Icecave\Dialekt\Parser\Exception\ParseException',
            'Unexpected AND operator, expected tag.'
        );

        $this->parser->parse('and');
    }

    public function testParseFailureWithWildcardCharacter()
    {
        $this->setExpectedException(
            'Icecave\Dialekt\Parser\Exception\ParseException',
            'Unexpected wildcard string "*", in tag "foo*".'
        );

        $this->parser->parse('foo*');
    }

    public function testTokens()
    {
        $lexer = new Lexer();
        $tokens = $lexer->lex('a b c');
        $result = $this->parser->parseTokens($tokens);

        $this->assertSame($tokens[0], $result->firstToken());
        $this->assertSame($tokens[2], $result->lastToken());

        $children = $result->children();

        $node = $children[0];
        $this->assertSame($tokens[0], $node->firstToken());
        $this->assertSame($tokens[0], $node->lastToken());

        $node = $children[1];
        $this->assertSame($tokens[1], $node->firstToken());
        $this->assertSame($tokens[1], $node->lastToken());

        $node = $children[2];
        $this->assertSame($tokens[2], $node->firstToken());
        $this->assertSame($tokens[2], $node->lastToken());
    }

    public function parseTestVectors()
    {
        return array(
            'empty expression' => array(
                '',
                new EmptyExpression(),
                array(),
            ),
            'single tag' => array(
                'foo',
                new Tag('foo'),
                array('foo'),
            ),
            'multiple tags' => array(
                'foo "bar spam"',
                new LogicalAnd(
                    new Tag('foo'),
                    new Tag('bar spam')
                ),
                array('foo', 'bar spam')
            ),
        );
    }
}
