<?php
namespace Icecave\Dialekt\Evaluator;

use Icecave\Dialekt\AST\ExpressionInterface;
use Phake;
use PHPUnit_Framework_TestCase;

class EvaluationResultTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->expression = Phake::mock('Icecave\Dialekt\AST\ExpressionInterface');

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
        $expression = Phake::mock('Icecave\Dialekt\AST\ExpressionInterface');

        $this->setExpectedException('UnexpectedValueException');
        $this->result->resultOf($expression);
    }
}
