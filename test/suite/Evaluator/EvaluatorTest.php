<?php

namespace Dialekt\Evaluator;

use Dialekt\AST\EmptyExpression;
use Dialekt\AST\ExpressionInterface;
use Dialekt\AST\LogicalAnd;
use Dialekt\AST\LogicalNot;
use Dialekt\AST\LogicalOr;
use Dialekt\AST\Pattern;
use Dialekt\AST\PatternLiteral;
use Dialekt\AST\PatternWildcard;
use Dialekt\AST\Tag;
use PHPUnit\Framework\TestCase;

class EvaluatorTest extends TestCase
{
    public function setUp(): void
    {
        $this->evaluator = new Evaluator();
    }

    /**
     * @dataProvider evaluateTestVectors
     */
    public function testEvaluate(ExpressionInterface $expression, $tags, $expected)
    {
        $result = $this->evaluator->evaluate($expression, $tags);

        $this->assertInstanceOf(
            'Dialekt\Evaluator\EvaluationResult',
            $result
        );

        $this->assertSame(
            $expected,
            $result->isMatch()
        );
    }

    public function testEvaluateTagCaseSensitive()
    {
        $this->evaluator = new Evaluator(true);
        $expression = new Tag('foo');

        $this->assertTrue(
            $this->evaluator->evaluate(
                $expression,
                ['foo']
            )->isMatch()
        );

        $this->assertFalse(
            $this->evaluator->evaluate(
                $expression,
                ['FOO']
            )->isMatch()
        );
    }

    public function testEvaluatePatternCaseSensitive()
    {
        $this->evaluator = new Evaluator(true);
        $expression = new Pattern(
            new PatternLiteral('foo'),
            new PatternWildcard()
        );

        $this->assertTrue(
            $this->evaluator->evaluate(
                $expression,
                ['foobar']
            )->isMatch()
        );

        $this->assertFalse(
            $this->evaluator->evaluate(
                $expression,
                ['FOOBAR']
            )->isMatch()
        );
    }

    public function testEvaluateEmptyExpressionEmptyAsWildcard()
    {
        $this->evaluator = new Evaluator(false, true);

        $this->assertTrue(
            $this->evaluator->evaluate(
                new EmptyExpression(),
                ['foo']
            )->isMatch()
        );
    }

    public function testEvaluateLogicalAnd()
    {
        $innerExpression1 = new Tag('foo');
        $innerExpression2 = new Tag('bar');
        $innerExpression3 = new Tag('bar');
        $expression = new LogicalAnd(
            $innerExpression1,
            $innerExpression2,
            $innerExpression3
        );

        $result = $this->evaluator->evaluate(
            $expression,
            ['foo', 'bar', 'spam']
        );

        $this->assertInstanceOf('Dialekt\Evaluator\EvaluationResult', $result);
        $this->assertTrue($result->isMatch());

        $expressionResult = $result->resultOf($expression);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(['foo', 'bar'], $expressionResult->matchedTags());
        $this->assertEquals(['spam'], $expressionResult->unmatchedTags());

        $expressionResult = $result->resultOf($innerExpression1);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(['foo'], $expressionResult->matchedTags());
        $this->assertEquals(['bar', 'spam'], $expressionResult->unmatchedTags());

        $expressionResult = $result->resultOf($innerExpression2);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(['bar'], $expressionResult->matchedTags());
        $this->assertEquals(['foo', 'spam'], $expressionResult->unmatchedTags());

        $expressionResult = $result->resultOf($innerExpression3);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(['bar'], $expressionResult->matchedTags());
        $this->assertEquals(['foo', 'spam'], $expressionResult->unmatchedTags());
    }

    public function testEvaluateLogicalOr()
    {
        $innerExpression1 = new Tag('foo');
        $innerExpression2 = new Tag('bar');
        $innerExpression3 = new Tag('doom');
        $expression = new LogicalOr(
            $innerExpression1,
            $innerExpression2,
            $innerExpression3
        );

        $result = $this->evaluator->evaluate(
            $expression,
            ['foo', 'bar', 'spam']
        );

        $this->assertInstanceOf('Dialekt\Evaluator\EvaluationResult', $result);
        $this->assertTrue($result->isMatch());

        $expressionResult = $result->resultOf($expression);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(['foo', 'bar'], $expressionResult->matchedTags());
        $this->assertEquals(['spam'], $expressionResult->unmatchedTags());

        $expressionResult = $result->resultOf($innerExpression1);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(['foo'], $expressionResult->matchedTags());
        $this->assertEquals(['bar', 'spam'], $expressionResult->unmatchedTags());

        $expressionResult = $result->resultOf($innerExpression2);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(['bar'], $expressionResult->matchedTags());
        $this->assertEquals(['foo', 'spam'], $expressionResult->unmatchedTags());

        $expressionResult = $result->resultOf($innerExpression3);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertFalse($expressionResult->isMatch());
        $this->assertEquals([], $expressionResult->matchedTags());
        $this->assertEquals(['foo', 'bar', 'spam'], $expressionResult->unmatchedTags());
    }

    public function testEvaluateLogicalNot()
    {
        $innerExpression = new Tag('foo');
        $expression = new LogicalNot($innerExpression);

        $result = $this->evaluator->evaluate(
            $expression,
            ['foo', 'bar']
        );

        $this->assertInstanceOf('Dialekt\Evaluator\EvaluationResult', $result);
        $this->assertFalse($result->isMatch());

        $expressionResult = $result->resultOf($expression);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertFalse($expressionResult->isMatch());
        $this->assertEquals(['bar'], $expressionResult->matchedTags());
        $this->assertEquals(['foo'], $expressionResult->unmatchedTags());

        $expressionResult = $result->resultOf($innerExpression);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(['foo'], $expressionResult->matchedTags());
        $this->assertEquals(['bar'], $expressionResult->unmatchedTags());
    }

    public function testEvaluateTag()
    {
        $expression = new Tag('foo');

        $result = $this->evaluator->evaluate(
            $expression,
            ['foo', 'bar']
        );

        $this->assertInstanceOf('Dialekt\Evaluator\EvaluationResult', $result);
        $this->assertTrue($result->isMatch());

        $expressionResult = $result->resultOf($expression);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(['foo'], $expressionResult->matchedTags());
        $this->assertEquals(['bar'], $expressionResult->unmatchedTags());
    }

    public function testEvaluatePattern()
    {
        $expression = new Pattern(
            new PatternLiteral('foo'),
            new PatternWildcard()
        );

        $result = $this->evaluator->evaluate(
            $expression,
            ['foo1', 'foo2', 'bar']
        );

        $this->assertInstanceOf('Dialekt\Evaluator\EvaluationResult', $result);
        $this->assertTrue($result->isMatch());

        $expressionResult = $result->resultOf($expression);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(['foo1', 'foo2'], $expressionResult->matchedTags());
        $this->assertEquals(['bar'], $expressionResult->unmatchedTags());
    }

    public function testEvaluateEmptyExpression()
    {
        $expression = new EmptyExpression();

        $result = $this->evaluator->evaluate(
            $expression,
            ['foo', 'bar']
        );

        $this->assertInstanceOf('Dialekt\Evaluator\EvaluationResult', $result);
        $this->assertFalse($result->isMatch());

        $expressionResult = $result->resultOf($expression);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertFalse($expressionResult->isMatch());
        $this->assertEquals([], $expressionResult->matchedTags());
        $this->assertEquals(['foo', 'bar'], $expressionResult->unmatchedTags());
    }

    public function evaluateTestVectors()
    {
        return [
            [
                new EmptyExpression(),
                ['foo'],
                false,
            ],
            [
                new Tag('foo'),
                ['foo'],
                true,
            ],
            [
                new Tag('foo'),
                ['bar'],
                false,
            ],
            [
                new Tag('foo'),
                ['foo', 'bar'],
                true,
            ],
            [
                new Pattern(
                    new PatternLiteral('foo'),
                    new PatternWildcard()
                ),
                ['foobar'],
                true,
            ],
            [
                new LogicalAnd(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                ['foo'],
                false,
            ],
            [
                new LogicalAnd(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                ['bar'],
                false,
            ],
            [
                new LogicalAnd(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                ['foo', 'bar'],
                true,
            ],
            [
                new LogicalAnd(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                ['foo', 'bar', 'spam'],
                true,
            ],
            [
                new LogicalAnd(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                ['foo', 'spam'],
                false,
            ],
            [
                new LogicalOr(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                ['foo'],
                true,
            ],
            [
                new LogicalOr(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                ['bar'],
                true,
            ],
            [
                new LogicalOr(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                ['foo', 'spam'],
                true,
            ],
            [
                new LogicalOr(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                ['spam'],
                false,
            ],
            [
                new LogicalNot(
                    new Tag('foo')
                ),
                ['foo'],
                false,
            ],
            [
                new LogicalNot(
                    new Tag('foo')
                ),
                ['foo', 'bar'],
                false,
            ],
            [
                new LogicalNot(
                    new Tag('foo')
                ),
                ['bar'],
                true,
            ],
            [
                new LogicalNot(
                    new Tag('foo')
                ),
                ['bar', 'spam'],
                true,
            ],
            [
                new LogicalAnd(
                    new Tag('foo'),
                    new LogicalNot(
                        new Tag('bar')
                    )
                ),
                ['foo'],
                true,
            ],
            [
                new LogicalAnd(
                    new Tag('foo'),
                    new LogicalNot(
                        new Tag('bar')
                    )
                ),
                ['foo', 'spam'],
                true,
            ],
            [
                new LogicalAnd(
                    new Tag('foo'),
                    new LogicalNot(
                        new Tag('bar')
                    )
                ),
                ['foo', 'bar', 'spam'],
                false,
            ],
            [
                new LogicalAnd(
                    new Tag('foo'),
                    new LogicalNot(
                        new Tag('bar')
                    )
                ),
                ['spam'],
                false,
            ],
        ];
    }
}
