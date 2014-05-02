<?php
namespace Icecave\Dialekt\Expression;

use Phake;
use PHPUnit_Framework_TestCase;

class LogicalNotTest extends PHPUnit_Framework_TestCase
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
        $visitor = Phake::mock('Icecave\Dialekt\Expression\VisitorInterface');

        Phake::when($visitor)
            ->visitLogicalNot(Phake::anyParameters())
            ->thenReturn('<visitor result>');

        $this->assertSame('<visitor result>', $this->expression->accept($visitor));
    }
}
