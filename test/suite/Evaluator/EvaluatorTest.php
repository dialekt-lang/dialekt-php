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
        $this->evaluator = new Evaluator;
    }

    /**
     * @dataProvider matchTestVectors
     */
    public function testMatch(ExpressionInterface $expression, $tag, $expected)
    {

        $this->assertSame(
            $expected,
            $this->evaluator->match($expression, $tag)
        );
    }

    public function testMatchTagCaseSensitive()
    {
        $this->evaluator = new Evaluator(true);
        $expression = new Tag('foo');

        $this->assertTrue($this->evaluator->match($expression, 'foo'));
        $this->assertFalse($this->evaluator->match($expression, 'FOO'));
    }

    public function testMatchPatternCaseSensitive()
    {
        $this->evaluator = new Evaluator(true);
        $expression = new Pattern(
            new PatternLiteral('foo'),
            new PatternWildcard
        );

        $this->assertTrue($this->evaluator->match($expression, 'foobar'));
        $this->assertFalse($this->evaluator->match($expression, 'FOOBAR'));
    }

    public function testMatchEmptyExpressionWithMatchAll()
    {
        $this->evaluator = new Evaluator(false, true);

        $this->assertTrue($this->evaluator->match(new EmptyExpression, 'foo'));
    }

    public function testMatchAll()
    {
        $expression = new LogicalOr(
            new Tag('foo'),
            new Tag('bar')
        );

        $this->assertTrue($this->evaluator->matchAll($expression, array('foo', 'bar')));
        $this->assertFalse($this->evaluator->matchAll($expression, array('foo', 'spam')));
    }

    public function testMatchAny()
    {
        $expression = new LogicalOr(
            new Tag('foo'),
            new Tag('bar')
        );

        $this->assertTrue($this->evaluator->matchAny($expression, array('foo', 'bar')));
        $this->assertTrue($this->evaluator->matchAny($expression, array('foo', 'spam')));
        $this->assertFalse($this->evaluator->matchAny($expression, array('doom', 'spam')));
    }

    public function testPartition()
    {
        $expression = new LogicalOr(
            new Tag('foo'),
            new Tag('bar')
        );

        list($matched, $notMatched) = $this->evaluator->partition(
            $expression,
            array(
                'spam',
                'foo',
                'doom',
                'bar'
            )
        );

        $this->assertEquals(
            array('foo', 'bar'),
            $matched
        );

        $this->assertEquals(
            array('spam', 'doom'),
            $notMatched
        );
    }

    public function matchTestVectors()
    {
        return array(
            'empty' => array(
                new EmptyExpression,
                'foo',
                false
            ),
            'tag match' => array(
                new Tag('foo'),
                'FoO', // differing case
                true
            ),
            'tag no-match' => array(
                new Tag('foo'),
                'bar',
                false
            ),
            'pattern match' => array(
                new Pattern(
                    new PatternWildcard,
                    new PatternLiteral('foo'),
                    new PatternWildcard
                ),
                '1FoO2', // differing case
                true
            ),
            'pattern no-match' => array(
                new Pattern(
                    new PatternLiteral('foo'),
                    new PatternWildcard
                ),
                'barfoospam',
                false
            ),
            'logical and match' => array(
                new LogicalAnd(
                    new Pattern(
                        new PatternLiteral('foo'),
                        new PatternWildcard
                    ),
                    new Pattern(
                        new PatternWildcard,
                        new PatternLiteral('bar')
                    )
                ),
                'foobar',
                true
            ),
            'logical and no-match' => array(
                new LogicalAnd(
                    new Pattern(
                        new PatternLiteral('foo'),
                        new PatternWildcard
                    ),
                    new Pattern(
                        new PatternWildcard,
                        new PatternLiteral('bar')
                    )
                ),
                'foo',
                false
            ),
            'logical or match 1' => array(
                new LogicalOr(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                'foo',
                true
            ),
            'logical or match 2' => array(
                new LogicalOr(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                'bar',
                true
            ),
            'logical or no-match' => array(
                new LogicalOr(
                    new Tag('foo'),
                    new Tag('bar')
                ),
                'spam',
                false
            ),
            'logical not match' => array(
                new LogicalNot(
                    new Tag('foo')
                ),
                'bar',
                true
            ),
            'logical not no-match' => array(
                new LogicalNot(
                    new Tag('foo')
                ),
                'foo',
                false
            ),
        );
    }
}
