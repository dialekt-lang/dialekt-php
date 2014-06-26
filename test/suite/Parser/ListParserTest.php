<?php
namespace Icecave\Dialekt\Parser;

use Icecave\Dialekt\AST\EmptyExpression;
use Icecave\Dialekt\AST\LogicalAnd;
use Icecave\Dialekt\AST\Tag;
use Icecave\Dialekt\Parser\Exception\ParseException;
use PHPUnit_Framework_TestCase;

/**
 * @covers Icecave\Dialekt\Parser\ListParser
 * @covers Icecave\Dialekt\Parser\AbstractParser
 */
class ListParserTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->parser = new ListParser;
    }

    public function testParseWithEmptyExpression()
    {
        $this->assertInstanceOf(
            'Icecave\Dialekt\AST\EmptyExpression',
            $this->parser->parse('')
        );
    }

    public function testParseWithSingleTag()
    {
        $this->assertEquals(
            new Tag('foo'),
            $this->parser->parse('foo')
        );
    }

    public function testParseWithMultipleTags()
    {
        $this->assertEquals(
            new LogicalAnd(
                new Tag('foo'),
                new Tag('bar spam')
            ),
            $this->parser->parse('foo "bar spam"')
        );
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

    public function testParseAsArrayWithEmptyExpression()
    {
        $this->assertEquals(
            array(),
            $this->parser->parseAsArray('')
        );
    }

    public function testParseAsArrayWithSingleTag()
    {
        $this->assertEquals(
            array('foo'),
            $this->parser->parseAsArray('foo')
        );
    }

    public function testParseAsArrayWithMultipleTags()
    {
        $this->assertEquals(
            array('foo', 'bar spam'),
            $this->parser->parseAsArray('foo "bar spam"')
        );
    }

    public function testParseWithSourceCapture()
    {
        $this->parser->setCaptureSource(true);

        $result = $this->parser->parse('a');

        $this->assertSame('a', $result->source());
        $this->assertSame(0, $result->sourceOffset());

        $result = $this->parser->parse('a b c');

        $this->assertSame('a b c', $result->source());
        $this->assertSame(0, $result->sourceOffset());

        $children = $result->children();

        $node = $children[0];
        $this->assertSame('a', $node->source());
        $this->assertSame(0, $node->sourceOffset());

        $node = $children[1];
        $this->assertSame('b', $node->source());
        $this->assertSame(2, $node->sourceOffset());

        $node = $children[2];
        $this->assertSame('c', $node->source());
        $this->assertSame(4, $node->sourceOffset());
    }
}
