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
    public function setUp()
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
                array('foo')
            )->isMatch()
        );

        $this->assertFalse(
            $this->evaluator->evaluate(
                $expression,
                array('FOO')
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
                array('foobar')
            )->isMatch()
        );

        $this->assertFalse(
            $this->evaluator->evaluate(
                $expression,
                array('FOOBAR')
            )->isMatch()
        );
    }

    public function testEvaluateEmptyExpressionEmptyAsWildcard()
    {
        $this->evaluator = new Evaluator(false, true);

        $this->assertTrue(
            $this->evaluator->evaluate(
                new EmptyExpression(),
                array('foo')
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
            array('foo', 'bar', 'spam')
        );

        $this->assertInstanceOf('Dialekt\Evaluator\EvaluationResult', $result);
        $this->assertTrue($result->isMatch());

        $expressionResult = $result->resultOf($expression);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(array('foo', 'bar'), $expressionResult->matchedTags());
        $this->assertEquals(array('spam'), $expressionResult->unmatchedTags());

        $expressionResult = $result->resultOf($innerExpression1);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(array('foo'), $expressionResult->matchedTags());
        $this->assertEquals(array('bar', 'spam'), $expressionResult->unmatchedTags());

        $expressionResult = $result->resultOf($innerExpression2);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(array('bar'), $expressionResult->matchedTags());
        $this->assertEquals(array('foo', 'spam'), $expressionResult->unmatchedTags());

        $expressionResult = $result->resultOf($innerExpression3);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(array('bar'), $expressionResult->matchedTags());
        $this->assertEquals(array('foo', 'spam'), $expressionResult->unmatchedTags());
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
            array('foo', 'bar', 'spam')
        );

        $this->assertInstanceOf('Dialekt\Evaluator\EvaluationResult', $result);
        $this->assertTrue($result->isMatch());

        $expressionResult = $result->resultOf($expression);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(array('foo', 'bar'), $expressionResult->matchedTags());
        $this->assertEquals(array('spam'), $expressionResult->unmatchedTags());

        $expressionResult = $result->resultOf($innerExpression1);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(array('foo'), $expressionResult->matchedTags());
        $this->assertEquals(array('bar', 'spam'), $expressionResult->unmatchedTags());

        $expressionResult = $result->resultOf($innerExpression2);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(array('bar'), $expressionResult->matchedTags());
        $this->assertEquals(array('foo', 'spam'), $expressionResult->unmatchedTags());

        $expressionResult = $result->resultOf($innerExpression3);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertFalse($expressionResult->isMatch());
        $this->assertEquals(array(), $expressionResult->matchedTags());
        $this->assertEquals(array('foo', 'bar', 'spam'), $expressionResult->unmatchedTags());
    }

    public function testEvaluateLogicalNot()
    {
        $innerExpression = new Tag('foo');
        $expression = new LogicalNot($innerExpression);

        $result = $this->evaluator->evaluate(
            $expression,
            array('foo', 'bar')
        );

        $this->assertInstanceOf('Dialekt\Evaluator\EvaluationResult', $result);
        $this->assertFalse($result->isMatch());

        $expressionResult = $result->resultOf($expression);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertFalse($expressionResult->isMatch());
        $this->assertEquals(array('bar'), $expressionResult->matchedTags());
        $this->assertEquals(array('foo'), $expressionResult->unmatchedTags());

        $expressionResult = $result->resultOf($innerExpression);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(array('foo'), $expressionResult->matchedTags());
        $this->assertEquals(array('bar'), $expressionResult->unmatchedTags());
    }

    public function testEvaluateTag()
    {
        $expression = new Tag('foo');

        $result = $this->evaluator->evaluate(
            $expression,
            array('foo', 'bar')
        );

        $this->assertInstanceOf('Dialekt\Evaluator\EvaluationResult', $result);
        $this->assertTrue($result->isMatch());

        $expressionResult = $result->resultOf($expression);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(array('foo'), $expressionResult->matchedTags());
        $this->assertEquals(array('bar'), $expressionResult->unmatchedTags());
    }

    public function testEvaluatePattern()
    {
        $expression = new Pattern(
            new PatternLiteral('foo'),
            new PatternWildcard()
        );

        $result = $this->evaluator->evaluate(
            $expression,
            array('foo1', 'foo2', 'bar')
        );

        $this->assertInstanceOf('Dialekt\Evaluator\EvaluationResult', $result);
        $this->assertTrue($result->isMatch());

        $expressionResult = $result->resultOf($expression);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertTrue($expressionResult->isMatch());
        $this->assertEquals(array('foo1', 'foo2'), $expressionResult->matchedTags());
        $this->assertEquals(array('bar'), $expressionResult->unmatchedTags());
    }

    public function testEvaluateEmptyExpression()
    {
        $expression = new EmptyExpression();

        $result = $this->evaluator->evaluate(
            $expression,
            array('foo', 'bar')
        );

        $this->assertInstanceOf('Dialekt\Evaluator\EvaluationResult', $result);
        $this->assertFalse($result->isMatch());

        $expressionResult = $result->resultOf($expression);
        $this->assertInstanceOf('Dialekt\Evaluator\ExpressionResult', $expressionResult);
        $this->assertFalse($expressionResult->isMatch());
        $this->assertEquals(array(), $expressionResult->matchedTags());
        $this->assertEquals(array('foo', 'bar'), $expressionResult->unmatchedTags());
    }

    public function evaluateTestVectors()
    {
        return array(
            array(
                new EmptyExpression(),
                array('foo'),
                false,
            ),
            array(
                new Tag('foo'),
                array('foo'),
                true,
            ),
            array(
                new Tag('foo'),
                array('bar'),
                false,
            ),
            array(
                new Tag('foo'),
                array('foo', 'bar'),
                true,
            ),
            array(
                new Pattern(
                    new PatternLiteral('foo'),
                    new PatternWildcard()
                ),
                array('foobar'),
                true,
            ),
            array(
                new LogicalAnd(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                array('foo'),
                false,
            ),
            array(
                new LogicalAnd(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                array('bar'),
                false,
            ),
            array(
                new LogicalAnd(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                array('foo', 'bar'),
                true,
            ),
            array(
                new LogicalAnd(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                array('foo', 'bar', 'spam'),
                true,
            ),
            array(
                new LogicalAnd(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                array('foo', 'spam'),
                false,
            ),
            array(
                new LogicalOr(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                array('foo'),
                true,
            ),
            array(
                new LogicalOr(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                array('bar'),
                true,
            ),
            array(
                new LogicalOr(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                array('foo', 'spam'),
                true,
            ),
            array(
                new LogicalOr(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                array('spam'),
                false,
            ),
            array(
                new LogicalNot(
                    new Tag('foo')
                ),
                array('foo'),
                false,
            ),
            array(
                new LogicalNot(
                    new Tag('foo')
                ),
                array('foo', 'bar'),
                false,
            ),
            array(
                new LogicalNot(
                    new Tag('foo')
                ),
                array('bar'),
                true,
            ),
            array(
                new LogicalNot(
                    new Tag('foo')
                ),
                array('bar', 'spam'),
                true,
            ),
            array(
                new LogicalAnd(
                    new Tag('foo'),
                    new LogicalNot(
                        new Tag('bar')
                    )
                ),
                array('foo'),
                true,
            ),
            array(
                new LogicalAnd(
                    new Tag('foo'),
                    new LogicalNot(
                        new Tag('bar')
                    )
                ),
                array('foo', 'spam'),
                true,
            ),
            array(
                new LogicalAnd(
                    new Tag('foo'),
                    new LogicalNot(
                        new Tag('bar')
                    )
                ),
                array('foo', 'bar', 'spam'),
                false,
            ),
            array(
                new LogicalAnd(
                    new Tag('foo'),
                    new LogicalNot(
                        new Tag('bar')
                    )
                ),
                array('spam'),
                false,
            ),
        );
    }
}
