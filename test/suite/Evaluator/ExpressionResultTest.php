<?php
namespace Icecave\Dialekt\Evaluator;

use Icecave\Dialekt\AST\ExpressionInterface;
use Phake;
use PHPUnit_Framework_TestCase;

class ExpressionResultTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->expression = Phake::mock('Icecave\Dialekt\AST\ExpressionInterface');

        $this->result = new ExpressionResult(
            $this->expression,
            true,
            array('foo'),
            array('bar')
        );
    }

    public function testExpression()
    {
        $this->assertSame($this->expression, $this->result->expression());
    }

    public function testIsMatch()
    {
        $this->assertTrue($this->result->isMatch());
    }

    public function testMatchedTags()
    {
        $this->assertEquals(
            array('foo'),
            $this->result->matchedTags()
        );
    }

    public function testUnmatchedTags()
    {
        $this->assertEquals(
            array('bar'),
            $this->result->unmatchedTags()
        );
    }
}
