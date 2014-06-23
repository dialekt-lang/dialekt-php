<?php
namespace Icecave\Dialekt\Parser;

use PHPUnit_Framework_TestCase;

class TokenTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $token = new Token(
            Token::STRING,
            'foo',
            1,
            2,
            3,
            4
        );

        $this->assertSame(Token::STRING, $token->type);
        $this->assertSame('foo', $token->value);
        $this->assertSame(1, $token->offset);
        $this->assertSame(2, $token->length);
        $this->assertSame(3, $token->line);
        $this->assertSame(4, $token->column);
    }

    /**
     * @dataProvider typeDescriptionTestVectors
     */
    public function testTypeDescription($type, $description)
    {
        $this->assertSame($description, Token::typeDescription($type));
    }

    public function testTypeDescriptionFailure()
    {
        $this->setExpectedException('LogicException');

        Token::typeDescription('unknown');
    }

    public function typeDescriptionTestVectors()
    {
        return array(
            array(
                Token::LOGICAL_AND,
                'AND operator',
            ),
            array(
                Token::LOGICAL_OR,
                'OR operator',
            ),
            array(
                Token::LOGICAL_NOT,
                'NOT operator',
            ),
            array(
                Token::STRING,
                'tag',
            ),
            array(
                Token::OPEN_BRACKET,
                'open bracket',
            ),
            array(
                Token::CLOSE_BRACKET,
                'close bracket',
            ),
        );
    }
}
