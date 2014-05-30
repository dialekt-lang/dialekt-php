<?php
namespace Icecave\Dialekt\AST;

use Phake;
use PHPUnit_Framework_TestCase;

class EmptyExpressionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->expression = new EmptyExpression;
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Icecave\Dialekt\AST\VisitorInterface');

        Phake::when($visitor)
            ->visitEmptyExpression(Phake::anyParameters())
            ->thenReturn('<visitor result>');

        $this->assertSame('<visitor result>', $this->expression->accept($visitor));
    }
}
