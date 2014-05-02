<?php
namespace Icecave\Dialekt\Parser;

use Icecave\Dialekt\Expression\EmptyExpression;
use Icecave\Dialekt\Expression\LogicalAnd;
use Icecave\Dialekt\Expression\LogicalNot;
use Icecave\Dialekt\Expression\LogicalOr;
use Icecave\Dialekt\Expression\Tag;
use Icecave\Dialekt\Expression\Wildcard;
use Icecave\Dialekt\Parser\Exception\ParseException;
use Icecave\Dialekt\Renderer\Renderer;

use PHPUnit_Framework_TestCase;

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->parser = new Parser;
        $this->renderer = new Renderer;
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
                new Wildcard('a*'),
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
            ),
            'invalid tag' => array(
                '-foo-',
                'Invalid tag: "-foo-".'
            ),
            'invalid wildcard' => array(
                '-foo*-',
                'Invalid wildcard: "-foo*-".'
            ),
        );
    }
}
