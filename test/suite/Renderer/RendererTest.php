<?php
namespace Icecave\Dialekt\Renderer;

use Icecave\Dialekt\Expression\EmptyExpression;
use Icecave\Dialekt\Expression\LogicalAnd;
use Icecave\Dialekt\Expression\LogicalNot;
use Icecave\Dialekt\Expression\LogicalOr;
use Icecave\Dialekt\Expression\Tag;
use Icecave\Dialekt\Expression\Wildcard;

use PHPUnit_Framework_TestCase;

class RendererTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->renderer = new Renderer;
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
            'wildcard' => array(
                new Wildcard('foo*'),
                'foo*',
            ),
            'escaped wildcard' => array(
                new Tag('f\\o"o*'),
                '"f\\\\o\\"o*"',
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
