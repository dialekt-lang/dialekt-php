<?php
namespace Dialekt\Evaluator;

use Dialekt\AST\ExpressionInterface;
use Phake;
use PHPUnit\Framework\TestCase;

class ExpressionResultTest extends TestCase
{
    public function setUp(): void
    {
        $this->expression = Phake::mock('Dialekt\AST\ExpressionInterface');

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
