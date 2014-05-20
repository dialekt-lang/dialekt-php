<?php
namespace Icecave\Dialekt\Evaluator;

use Icecave\Dialekt\AST\EmptyExpression;
use Icecave\Dialekt\AST\ExpressionInterface;
use Icecave\Dialekt\AST\LogicalAnd;
use Icecave\Dialekt\AST\LogicalNot;
use Icecave\Dialekt\AST\LogicalOr;
use Icecave\Dialekt\AST\Pattern;
use Icecave\Dialekt\AST\PatternLiteral;
use Icecave\Dialekt\AST\PatternWildcard;
use Icecave\Dialekt\AST\Tag;
use PHPUnit_Framework_TestCase;

class EvaluatorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->visitor = new Evaluator;
    }

    /**
     * @dataProvider evaluateTestVectors
     */
    public function testEvaluate(ExpressionInterface $expression, $tags, $expected)
    {
        $this->assertSame(
            $expected,
            $this->visitor->evaluate($expression, $tags)
        );
    }

    public function testMatchTagCaseSensitive()
    {
        $this->evaluator = new Evaluator(true);
        $expression = new Tag('foo');

        $this->assertTrue(
            $this->evaluator->evaluate(
                $expression,
                array('foo')
            )
        );

        $this->assertFalse(
            $this->evaluator->evaluate(
                $expression,
                array('FOO')
            )
        );
    }
    public function testMatchPatternCaseSensitive()
    {
        $this->evaluator = new Evaluator(true);
        $expression = new Pattern(
            new PatternLiteral('foo'),
            new PatternWildcard
        );

        $this->assertTrue(
            $this->evaluator->evaluate(
                $expression,
                array('foobar')
            )
        );

        $this->assertFalse(
            $this->evaluator->evaluate(
                $expression,
                array('FOOBAR')
            )
        );
    }

    public function testMatchEmptyExpressionEmptyAsWildcard()
    {
        $this->evaluator = new Evaluator(false, true);

        $this->assertTrue(
            $this->evaluator->evaluate(
                new EmptyExpression,
                array('foo')
            )
        );
    }

    public function evaluateTestVectors()
    {
        return array(
            array(
                new EmptyExpression,
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
                    new PatternWildcard
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
