<?php
namespace Dialekt\Evaluator;

use Dialekt\AST\ExpressionInterface;
use Phake;
use PHPUnit\Framework\TestCase;

class EvaluationResultTest extends TestCase
{
    public function setUp()
    {
        $this->expression = Phake::mock('Dialekt\AST\ExpressionInterface');

        $this->expressionResult = new ExpressionResult(
            $this->expression,
            true,
            array(),
            array()
        );

        $this->result = new EvaluationResult(
            true,
            array($this->expressionResult)
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
