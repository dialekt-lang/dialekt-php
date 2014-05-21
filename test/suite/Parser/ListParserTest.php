<?php
namespace Icecave\Dialekt\Parser;

use Icecave\Dialekt\AST\EmptyExpression;
use Icecave\Dialekt\AST\LogicalAnd;
use Icecave\Dialekt\AST\Tag;
use Icecave\Dialekt\Parser\Exception\ParseException;

use PHPUnit_Framework_TestCase;

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
            'Unexpected AND operator, expected tag or end of input.'
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
}
