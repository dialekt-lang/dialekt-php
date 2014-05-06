<?php
namespace Icecave\Dialekt\Renderer;

use Icecave\Dialekt\AST\EmptyExpression;
use Icecave\Dialekt\AST\LogicalAnd;
use Icecave\Dialekt\AST\LogicalNot;
use Icecave\Dialekt\AST\LogicalOr;
use Icecave\Dialekt\AST\Pattern;
use Icecave\Dialekt\AST\PatternLiteral;
use Icecave\Dialekt\AST\PatternWildcard;
use Icecave\Dialekt\AST\Tag;

use PHPUnit_Framework_TestCase;

class ExpressionRendererTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->renderer = new ExpressionRenderer;
    }

    /**
     * @dataProvider renderTestVectors
     */
    public function testRender($expression, $expectedString)
    {
        $string = $this->renderer->render($expression);

        $this->assertSame($expectedString, $string);
    }

    public function renderTestVectors()
    {
        return array(
            'empty expression' => array(
                new EmptyExpression,
                'NOT *',
            ),
            'tag' => array(
                new Tag('foo'),
                'foo',
            ),
            'escaped tag' => array(
                new Tag('f\\o"o'),
                '"f\\\\o\\"o"',
            ),
            'escaped tag - logical and' => array(
                new Tag('and'),
                '"and"',
            ),
            'escaped tag - logical or' => array(
                new Tag('or'),
                '"or"',
            ),
            'escaped tag - logical not' => array(
                new Tag('not'),
                '"not"',
            ),
            'tag with spaces' => array(
                new Tag('foo bar'),
                '"foo bar"',
            ),
            'pattern' => array(
                new Pattern(
                    new PatternLiteral('foo'),
                    new PatternWildcard
                ),
                'foo*',
            ),
            'escaped pattern' => array(
                new Pattern(
                    new PatternLiteral('foo"'),
                    new PatternWildcard
                ),
                '"foo\\"*"',
            ),
            'logical and' => array(
                new LogicalAnd(
                    new Tag('a'),
                    new Tag('b'),
                    new Tag('c')
                ),
                '(a AND b AND c)',
            ),
            'logical or' => array(
                new LogicalOr(
                    new Tag('a'),
                    new Tag('b'),
                    new Tag('c')
                ),
                '(a OR b OR c)',
            ),
            'logical not' => array(
                new LogicalNot(
                    new Tag('a')
                ),
                'NOT a',
            ),
        );
    }
}
