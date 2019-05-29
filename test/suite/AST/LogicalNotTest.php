<?php
namespace Dialekt\AST;

use Phake;
use PHPUnit\Framework\TestCase;

class LogicalNotTest extends TestCase
{
    public function setUp()
    {
        $this->child = new Tag('foo');
        $this->expression = new LogicalNot($this->child);
    }

    public function testChild()
    {
        $this->assertSame($this->child, $this->expression->child());
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Dialekt\AST\VisitorInterface');

        Phake::when($visitor)
            ->visitLogicalNot(Phake::anyParameters())
            ->thenReturn('<visitor result>');

        $this->assertSame('<visitor result>', $this->expression->accept($visitor));
    }
}
