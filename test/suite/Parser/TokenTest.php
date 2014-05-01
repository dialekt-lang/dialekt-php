<?php
namespace Icecave\Dialekt\Parser;

use PHPUnit_Framework_TestCase;

class TokenTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $token = new Token(Token::STRING, 'foo');

        $this->assertSame(Token::STRING, $token->type);
        $this->assertSame('foo', $token->value);
    }
}
