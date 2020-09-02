<?php

namespace Dialekt\Parser;

use Dialekt\AST\EmptyExpression;
use Dialekt\AST\LogicalAnd;
use Dialekt\AST\LogicalNot;
use Dialekt\AST\LogicalOr;
use Dialekt\AST\Pattern;
use Dialekt\AST\PatternLiteral;
use Dialekt\AST\PatternWildcard;
use Dialekt\AST\Tag;
use Dialekt\Parser\Exception\ParseException;
use Dialekt\Renderer\ExpressionRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers Dialekt\Parser\ExpressionParser
 * @covers Dialekt\Parser\AbstractParser
 */
class ExpressionParserTest extends TestCase
{
    public function setUp(): void
    {
        $this->renderer = new ExpressionRenderer();
        $this->parser = new ExpressionParser();
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
        $lexer = new Lexer();
        $tokens = $lexer->lex('a AND (b OR c) AND NOT p*');
        $result = $this->parser->parseTokens($tokens);

        $this->assertSame($tokens[0], $result->firstToken());
        $this->assertSame($tokens[9], $result->lastToken());

        $children = $result->children();
        $node = $children[0];
        $this->assertSame($tokens[0], $node->firstToken());
        $this->assertSame($tokens[0], $node->lastToken());

        $node = $children[1];
        $this->assertSame($tokens[2], $node->firstToken());
        $this->assertSame($tokens[6], $node->lastToken());

        $node = $children[2];
        $this->assertSame($tokens[8], $node->firstToken());
        $this->assertSame($tokens[9], $node->lastToken());

        $node = $children[2]->child();
        $this->assertSame($tokens[9], $node->firstToken());
        $this->assertSame($tokens[9], $node->lastToken());

        $children = $children[1]->children();
        $node = $children[0];
        $this->assertSame($tokens[3], $node->firstToken());
        $this->assertSame($tokens[3], $node->lastToken());

        $node = $children[1];
        $this->assertSame($tokens[5], $node->firstToken());
        $this->assertSame($tokens[5], $node->lastToken());
    }

    public function parseTestVectors()
    {
        return [
            'empty expression' => [
                '',
                new EmptyExpression(),
            ],
            'single tag' => [
                'a',
                new Tag('a'),
            ],
            'tag pattern' => [
                'a*',
                new Pattern(
                    new PatternLiteral('a'),
                    new PatternWildcard()
                ),
            ],
            'multiple tags' => [
                'a b c',
                new LogicalAnd(
                    new Tag('a'),
                    new Tag('b'),
                    new Tag('c')
                ),
            ],
            'multiple tags with nesting' => [
                'a (b c)',
                new LogicalAnd(
                    new Tag('a'),
                    new LogicalAnd(
                        new Tag('b'),
                        new Tag('c')
                    )
                ),
            ],
            'multiple nested groups remain nested' => [
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
                ),
            ],
            'logical and' => [
                'a AND b',
                new LogicalAnd(
                    new Tag('a'),
                    new Tag('b')
                ),
            ],
            'logical and chained' => [
                'a AND b AND c',
                new LogicalAnd(
                    new Tag('a'),
                    new Tag('b'),
                    new Tag('c')
                ),
            ],
            'logical or' => [
                'a OR b',
                new LogicalOr(
                    new Tag('a'),
                    new Tag('b')
                ),
            ],
            'logical or chained' => [
                'a OR b OR c',
                new LogicalOr(
                    new Tag('a'),
                    new Tag('b'),
                    new Tag('c')
                ),
            ],
            'logical not' => [
                'NOT a',
                new LogicalNot(
                    new Tag('a')
                ),
            ],
            'logical not chained' => [
                'NOT NOT a',
                new LogicalNot(
                    new LogicalNot(
                        new Tag('a')
                    )
                ),
            ],
            'logical operator implicit precedence 1' => [
                'a OR b AND c',
                new LogicalOr(
                    new Tag('a'),
                    new LogicalAnd(
                        new Tag('b'),
                        new Tag('c')
                    )
                ),
            ],
            'logical operator implicit precedence 2' => [
                'a AND b OR c',
                new LogicalOr(
                    new LogicalAnd(
                        new Tag('a'),
                        new Tag('b')
                    ),
                    new Tag('c')
                ),
            ],
            'logical operator explicit precedence 1' => [
                '(a OR b) AND c',
                new LogicalAnd(
                    new LogicalOr(
                        new Tag('a'),
                        new Tag('b')
                    ),
                    new Tag('c')
                ),
            ],
            'logical operator explicit precedence 2' => [
                'a AND (b OR c)',
                new LogicalAnd(
                    new Tag('a'),
                    new LogicalOr(
                        new Tag('b'),
                        new Tag('c')
                    )
                ),
            ],
            'logical not implicit precedence' => [
                'NOT a AND b',
                new LogicalAnd(
                    new LogicalNot(
                        new Tag('a')
                    ),
                    new Tag('b')
                ),
            ],
            'logical not explicit precedence' => [
                'NOT (a AND b)',
                new LogicalNot(
                    new LogicalAnd(
                        new Tag('a'),
                        new Tag('b')
                    )
                ),
            ],
            'complex nested' => [
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
                ),
            ],
        ];
    }

    public function parseFailureTestVectors()
    {
        return [
            'leading logical and' => [
                'AND a',
                'Unexpected AND operator, expected tag, NOT operator or open bracket.',
            ],
            'leading logical or' => [
                'OR a',
                'Unexpected OR operator, expected tag, NOT operator or open bracket.',
            ],
            'trailing logical and' => [
                'a AND',
                'Unexpected end of input, expected tag, NOT operator or open bracket.',
            ],
            'trailing logical or' => [
                'a OR',
                'Unexpected end of input, expected tag, NOT operator or open bracket.',
            ],
            'mismatched braces 1' => [
                '(a',
                'Unexpected end of input, expected close bracket.',
            ],
            'mismatched braces 2' => [
                'a)',
                'Unexpected close bracket, expected end of input.',
            ],
        ];
    }
}
