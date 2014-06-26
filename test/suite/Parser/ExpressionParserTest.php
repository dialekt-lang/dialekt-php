<?php
namespace Icecave\Dialekt\Parser;

use Icecave\Dialekt\AST\EmptyExpression;
use Icecave\Dialekt\AST\LogicalAnd;
use Icecave\Dialekt\AST\LogicalNot;
use Icecave\Dialekt\AST\LogicalOr;
use Icecave\Dialekt\AST\Pattern;
use Icecave\Dialekt\AST\PatternLiteral;
use Icecave\Dialekt\AST\PatternWildcard;
use Icecave\Dialekt\AST\Tag;
use Icecave\Dialekt\Parser\Exception\ParseException;
use Icecave\Dialekt\Renderer\ExpressionRenderer;
use PHPUnit_Framework_TestCase;

/**
 * @covers Icecave\Dialekt\Parser\ExpressionParser
 * @covers Icecave\Dialekt\Parser\AbstractParser
 */
class ExpressionParserTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->parser = new ExpressionParser;
        $this->renderer = new ExpressionRenderer;
    }

    /**
     * @dataProvider parseTestVectors
     */
    public function testParse($expression, $expectedResult)
    {
        $result = $this->parser->parse($expression);

        $this->assertEquals(
            $this->renderer->render($expectedResult),
            $this->renderer->render($result)
        );

        if ($expectedResult !== $result) {
            $this->assertEquals($expectedResult, $result);
        }
    }

    /**
     * @dataProvider parseFailureTestVectors
     */
    public function testParseFailure($expression, $expectedMessage)
    {
        try {
            $this->parser->parse($expression);
            $this->fail('Expected exception was not thrown.');
        } catch (ParseException $e) {
            $this->assertEquals($expectedMessage, $e->getMessage());
        }
    }

    public function testParseUsingLogicalOrAsDefaultOperator()
    {
        $this->parser->setLogicalOrByDefault(true);

        $result = $this->parser->parse('a and b c and d');

        $this->assertEquals(
            '((a AND b) OR (c AND d))',
            $this->renderer->render($result)
        );
    }

    public function testParseWithSourceCapture()
    {
        $this->parser->setCaptureSource(true);

        $result = $this->parser->parse('a AND (b OR c) AND NOT p*');

        $this->assertSame('a AND (b OR c) AND NOT p*', $result->source());
        $this->assertSame(0, $result->sourceOffset());

        $children = $result->children();

        $node = $children[0];
        $this->assertSame('a', $node->source());
        $this->assertSame(0, $node->sourceOffset());

        $node = $children[1];
        $this->assertSame('(b OR c)', $node->source());
        $this->assertSame(6, $node->sourceOffset());

        $node = $children[2];
        $this->assertSame('NOT p*', $node->source());
        $this->assertSame(19, $node->sourceOffset());

        $node = $children[2]->child();
        $this->assertSame('p*', $node->source());
        $this->assertSame(23, $node->sourceOffset());

        $children = $children[1]->children();
        $node = $children[0];
        $this->assertSame('b', $node->source());
        $this->assertSame(7, $node->sourceOffset());

        $node = $children[1];
        $this->assertSame('c', $node->source());
        $this->assertSame(12, $node->sourceOffset());
    }

    public function testParseEmptyExpressionWithSourceCapture()
    {
        $this->parser->setCaptureSource(true);

        $result = $this->parser->parse('');

        $this->assertInstanceOf('Icecave\Dialekt\AST\EmptyExpression', $result);

        $this->assertSame('', $result->source());
        $this->assertSame(0, $result->sourceOffset());
    }

    public function parseTestVectors()
    {
        return array(
            'empty expression' => array(
                '',
                new EmptyExpression,
            ),
            'single tag' => array(
                'a',
                new Tag('a'),
            ),
            'tag pattern' => array(
                'a*',
                new Pattern(
                    new PatternLiteral('a'),
                    new PatternWildcard
                ),
            ),
            'multiple tags' => array(
                'a b c',
                new LogicalAnd(
                    new Tag('a'),
                    new Tag('b'),
                    new Tag('c')
                ),
            ),
            'multiple tags with nesting' => array(
                'a (b c)',
                new LogicalAnd(
                    new Tag('a'),
                    new LogicalAnd(
                        new Tag('b'),
                        new Tag('c')
                    )
                ),
            ),
            'multiple nested groups remain nested' => array(
                '(a b) (c d)',
                new LogicalAnd(
                    new LogicalAnd(
                        new Tag('a'),
                        new Tag('b')
                    ),
                    new LogicalAnd(
                        new Tag('c'),
                        new Tag('d')
                    )
                )
            ),
            'logical and' => array(
                'a AND b',
                new LogicalAnd(
                    new Tag('a'),
                    new Tag('b')
                ),
            ),
            'logical and chained' => array(
                'a AND b AND c',
                new LogicalAnd(
                    new Tag('a'),
                    new Tag('b'),
                    new Tag('c')
                ),
            ),
            'logical or' => array(
                'a OR b',
                new LogicalOr(
                    new Tag('a'),
                    new Tag('b')
                ),
            ),
            'logical or chained' => array(
                'a OR b OR c',
                new LogicalOr(
                    new Tag('a'),
                    new Tag('b'),
                    new Tag('c')
                ),
            ),
            'logical not' => array(
                'NOT a',
                new LogicalNot(
                    new Tag('a')
                ),
            ),
            'logical not chained' => array(
                'NOT NOT a',
                new LogicalNot(
                    new LogicalNot(
                        new Tag('a')
                    )
                ),
            ),
            'logical operator implicit precedence 1' => array(
                'a OR b AND c',
                new LogicalOr(
                    new Tag('a'),
                    new LogicalAnd(
                        new Tag('b'),
                        new Tag('c')
                    )
                ),
            ),
            'logical operator implicit precedence 2' => array(
                'a AND b OR c',
                new LogicalOr(
                    new LogicalAnd(
                        new Tag('a'),
                        new Tag('b')
                    ),
                    new Tag('c')
                ),
            ),
            'logical operator explicit precedence 1' => array(
                '(a OR b) AND c',
                new LogicalAnd(
                    new LogicalOr(
                        new Tag('a'),
                        new Tag('b')
                    ),
                    new Tag('c')
                ),
            ),
            'logical operator explicit precedence 2' => array(
                'a AND (b OR c)',
                new LogicalAnd(
                    new Tag('a'),
                    new LogicalOr(
                        new Tag('b'),
                        new Tag('c')
                    )
                ),
            ),
            'logical not implicit precedence' => array(
                'NOT a AND b',
                new LogicalAnd(
                    new LogicalNot(
                        new Tag('a')
                    ),
                    new Tag('b')
                )
            ),
            'logical not explicit precedence' => array(
                'NOT (a AND b)',
                new LogicalNot(
                    new LogicalAnd(
                        new Tag('a'),
                        new Tag('b')
                    )
                )
            ),
            'complex nested' => array(
                'a ((b OR c) AND (d OR e)) f',
                new LogicalAnd(
                    new Tag('a'),
                    new LogicalAnd(
                        new LogicalOr(
                            new Tag('b'),
                            new Tag('c')
                        ),
                        new LogicalOr(
                            new Tag('d'),
                            new Tag('e')
                        )
                    ),
                    new Tag('f')
                )
            ),
        );
    }

    public function parseFailureTestVectors()
    {
        return array(
            'leading logical and' => array(
                'AND a',
                'Unexpected AND operator, expected tag, NOT operator or open bracket.',
            ),
            'leading logical or' => array(
                'OR a',
                'Unexpected OR operator, expected tag, NOT operator or open bracket.',
            ),
            'trailing logical and' => array(
                'a AND',
                'Unexpected end of input, expected tag, NOT operator or open bracket.',
            ),
            'trailing logical or' => array(
                'a OR',
                'Unexpected end of input, expected tag, NOT operator or open bracket.',
            ),
            'mismatched braces 1' => array(
                '(a',
                'Unexpected end of input, expected close bracket.'
            ),
            'mismatched braces 2' => array(
                'a)',
                'Unexpected close bracket, expected end of input.'
            )
        );
    }
}
