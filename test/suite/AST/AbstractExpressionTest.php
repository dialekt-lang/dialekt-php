<?php
namespace Icecave\Dialekt\AST;

use Phake;
use PHPUnit_Framework_TestCase;

class AbstractExpressionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->node = Phake::partialMock('Icecave\Dialekt\AST\AbstractExpression');
    }

    public function testDefaults()
    {
        $this->assertNull($this->node->firstToken());
        $this->assertNull($this->node->lastToken());
    }

    public function testSetTokens()
    {
        $firstToken = Phake::mock('Icecave\Dialekt\Parser\Token');
        $lastToken = Phake::mock('Icecave\Dialekt\Parser\Token');

        $this->node->setTokens($firstToken, $lastToken);

        $this->assertSame($firstToken, $this->node->firstToken());
        $this->assertSame($lastToken, $this->node->lastToken());
    }
}
