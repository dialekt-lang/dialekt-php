<?php
namespace Dialekt\AST;

use Phake;
use PHPUnit\Framework\TestCase;

class PatternLiteralTest extends TestCase
{
    public function setUp()
    {
        $this->node = new PatternLiteral('foo');
    }

    public function testString()
    {
        $this->assertSame('foo', $this->node->string());
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Dialekt\AST\VisitorInterface');

        Phake::when($visitor)
            ->visitPatternLiteral(Phake::anyParameters())
            ->thenReturn('<visitor result>');

        $this->assertSame('<visitor result>', $this->node->accept($visitor));
    }
}
