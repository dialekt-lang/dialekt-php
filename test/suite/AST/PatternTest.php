<?php
namespace Dialekt\AST;

use Phake;
use PHPUnit\Framework\TestCase;

class PatternTest extends TestCase
{
    public function setUp()
    {
        $this->child1 = new PatternLiteral('foo');
        $this->child2 = new PatternWildcard();
        $this->child3 = new PatternLiteral('bar');
        $this->expression = new Pattern($this->child1, $this->child2);
    }

    public function testAdd()
    {
        $this->expression->add($this->child3);

        $this->assertSame(
            array($this->child1, $this->child2, $this->child3),
            $this->expression->children()
        );
    }

    public function testChildren()
    {
        $this->assertSame(
            array($this->child1, $this->child2),
            $this->expression->children()
        );
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Dialekt\AST\VisitorInterface');

        Phake::when($visitor)
            ->visitPattern(Phake::anyParameters())
            ->thenReturn('<visitor result>');

        $this->assertSame('<visitor result>', $this->expression->accept($visitor));
    }
}
