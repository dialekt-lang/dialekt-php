<?php

namespace Dialekt\Evaluator;

use Phake;
use PHPUnit\Framework\TestCase;

class EvaluationResultTest extends TestCase
{
    public function setUp(): void
    {
        $this->expression = Phake::mock('Dialekt\AST\ExpressionInterface');

        $this->expressionResult = new ExpressionResult(
            $this->expression,
            true,
            [],
            []
        );

        $this->result = new EvaluationResult(
            true,
            [$this->expressionResult]
        );
    }

    public function testIsMatch()
    {
        $this->assertTrue($this->result->isMatch());
    }

    public function testResultOf()
    {
        $this->assertSame(
            $this->expressionResult,
            $this->result->resultOf($this->expression)
        );
    }

    public function testResultOfWithUnknownExpression()
    {
        $expression = Phake::mock('Dialekt\AST\ExpressionInterface');

        $this->expectException('UnexpectedValueException');
        $this->result->resultOf($expression);
    }
}
