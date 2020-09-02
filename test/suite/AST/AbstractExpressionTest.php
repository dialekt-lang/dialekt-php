<?php

namespace Dialekt\AST;

use Phake;
use PHPUnit\Framework\TestCase;

class AbstractExpressionTest extends TestCase
{
    public function setUp(): void
    {
        $this->node = Phake::partialMock('Dialekt\AST\AbstractExpression');
    }

    public function testDefaults()
    {
        $this->assertNull($this->node->firstToken());
        $this->assertNull($this->node->lastToken());
    }

    public function testSetTokens()
    {
        $firstToken = Phake::mock('Dialekt\Parser\Token');
        $lastToken = Phake::mock('Dialekt\Parser\Token');

        $this->node->setTokens($firstToken, $lastToken);

        $this->assertSame($firstToken, $this->node->firstToken());
        $this->assertSame($lastToken, $this->node->lastToken());
    }
}
