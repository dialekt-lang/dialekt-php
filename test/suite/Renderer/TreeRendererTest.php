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

class TreeRendererTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->renderer = new TreeRenderer;
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
                'EMPTY',
            ),
            'tag' => array(
                new Tag('foo'),
                'TAG "foo"',
            ),
            'escaped tag' => array(
                new Tag('f\\o"o'),
                'TAG "f\\\\o\\"o"',
            ),
            'escaped tag - logical and' => array(
                new Tag('and'),
                'TAG "and"',
            ),
            'escaped tag - logical or' => array(
                new Tag('or'),
                'TAG "or"',
            ),
            'escaped tag - logical not' => array(
                new Tag('not'),
                'TAG "not"',
            ),
            'tag with spaces' => array(
                new Tag('foo bar'),
                'TAG "foo bar"',
            ),
            'pattern' => array(
                new Pattern(
                    new PatternLiteral('foo'),
                    new PatternWildcard
                ),
                'PATTERN' . PHP_EOL .
                '  - LITERAL "foo"' . PHP_EOL .
                '  - WILDCARD',
            ),
            'escaped pattern' => array(
                new Pattern(
                    new PatternLiteral('foo"'),
                    new PatternWildcard
                ),
                'PATTERN' . PHP_EOL .
                '  - LITERAL "foo\\""' . PHP_EOL .
                '  - WILDCARD',
            ),
            'logical and' => array(
                new LogicalAnd(
                    new Tag('a'),
                    new Tag('b'),
                    new Tag('c')
                ),
                'AND' . PHP_EOL .
                '  - TAG "a"' . PHP_EOL .
                '  - TAG "b"' . PHP_EOL .
                '  - TAG "c"',
            ),
            'logical or' => array(
                new LogicalOr(
                    new Tag('a'),
                    new Tag('b'),
                    new Tag('c')
                ),
                'OR' . PHP_EOL .
                '  - TAG "a"' . PHP_EOL .
                '  - TAG "b"' . PHP_EOL .
                '  - TAG "c"',
            ),
            'logical not' => array(
                new LogicalNot(
                    new Tag('a')
                ),
                'NOT' . PHP_EOL .
                '  - TAG "a"',
            ),
        );
    }
}
