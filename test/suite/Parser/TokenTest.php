<?php
namespace Dialekt\Parser;

use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
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
        $this->assertSame(1, $token->startOffset);
        $this->assertSame(2, $token->endOffset);
        $this->assertSame(3, $token->lineNumber);
        $this->assertSame(4, $token->columnNumber);
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
        $this->expectException('LogicException');

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
