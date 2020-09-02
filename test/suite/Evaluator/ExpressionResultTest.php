<?php

namespace Dialekt\Evaluator;

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
            ['foo'],
            ['bar']
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
            ['foo'],
            $this->result->matchedTags()
        );
    }

    public function testUnmatchedTags()
    {
        $this->assertEquals(
            ['bar'],
            $this->result->unmatchedTags()
        );
    }
}
