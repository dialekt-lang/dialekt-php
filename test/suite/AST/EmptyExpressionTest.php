<?php
namespace Dialekt\AST;

use Phake;
use PHPUnit\Framework\TestCase;

class EmptyExpressionTest extends TestCase
{
    public function setUp()
    {
        $this->expression = new EmptyExpression();
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Dialekt\AST\VisitorInterface');

        Phake::when($visitor)
            ->visitEmptyExpression(Phake::anyParameters())
            ->thenReturn('<visitor result>');

        $this->assertSame('<visitor result>', $this->expression->accept($visitor));
    }
}
