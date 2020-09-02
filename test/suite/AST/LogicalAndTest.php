<?php

namespace Dialekt\AST;

use Phake;
use PHPUnit\Framework\TestCase;

/**
 * @covers Dialekt\AST\LogicalAnd
 * @covers Dialekt\AST\AbstractPolyadicExpression
 */
class LogicalAndTest extends TestCase
{
    public function setUp(): void
    {
        $this->child1 = new Tag('a');
        $this->child2 = new Tag('b');
        $this->child3 = new Tag('c');
        $this->expression = new LogicalAnd($this->child1, $this->child2);
    }

    public function testAdd()
    {
        $this->expression->add($this->child3);

        $this->assertSame(
            [$this->child1, $this->child2, $this->child3],
            $this->expression->children()
        );
    }

    public function testChildren()
    {
        $this->assertSame(
            [$this->child1, $this->child2],
            $this->expression->children()
        );
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Dialekt\AST\VisitorInterface');

        Phake::when($visitor)
            ->visitLogicalAnd(Phake::anyParameters())
            ->thenReturn('<visitor result>');

        $this->assertSame('<visitor result>', $this->expression->accept($visitor));
    }
}
