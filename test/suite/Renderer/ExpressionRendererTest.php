<?php

namespace Dialekt\Renderer;

use Dialekt\AST\EmptyExpression;
use Dialekt\AST\LogicalAnd;
use Dialekt\AST\LogicalNot;
use Dialekt\AST\LogicalOr;
use Dialekt\AST\Pattern;
use Dialekt\AST\PatternLiteral;
use Dialekt\AST\PatternWildcard;
use Dialekt\AST\Tag;

use PHPUnit\Framework\TestCase;

class ExpressionRendererTest extends TestCase
{
    public function setUp(): void
    {
        $this->renderer = new ExpressionRenderer();
    }

    /**
     * @dataProvider renderTestVectors
     */
    public function testRender($expression, $expectedString)
    {
        $string = $this->renderer->render($expression);

        $this->assertSame($expectedString, $string);
    }

    public function testRenderFailureWithWildcardInPatternLiteral()
    {
        $this->expectException(
            'Dialekt\Renderer\Exception\RenderException',
            'The pattern literal "foo*" contains the wildcard string "*".'
        );

        $this->renderer->render(
            new Pattern(
                new PatternLiteral('foo*')
            )
        );
    }

    public function renderTestVectors()
    {
        return [
            'empty expression' => [
                new EmptyExpression(),
                'NOT *',
            ],
            'tag' => [
                new Tag('foo'),
                'foo',
            ],
            'escaped tag' => [
                new Tag('f\\o"o'),
                '"f\\\\o\\"o"',
            ],
            'escaped tag - logical and' => [
                new Tag('and'),
                '"and"',
            ],
            'escaped tag - logical or' => [
                new Tag('or'),
                '"or"',
            ],
            'escaped tag - logical not' => [
                new Tag('not'),
                '"not"',
            ],
            'tag with spaces' => [
                new Tag('foo bar'),
                '"foo bar"',
            ],
            'pattern' => [
                new Pattern(
                    new PatternLiteral('foo'),
                    new PatternWildcard()
                ),
                'foo*',
            ],
            'escaped pattern' => [
                new Pattern(
                    new PatternLiteral('foo"'),
                    new PatternWildcard()
                ),
                '"foo\\"*"',
            ],
            'logical and' => [
                new LogicalAnd(
                    new Tag('a'),
                    new Tag('b'),
                    new Tag('c')
                ),
                '(a AND b AND c)',
            ],
            'logical or' => [
                new LogicalOr(
                    new Tag('a'),
                    new Tag('b'),
                    new Tag('c')
                ),
                '(a OR b OR c)',
            ],
            'logical not' => [
                new LogicalNot(
                    new Tag('a')
                ),
                'NOT a',
            ],
        ];
    }
}
