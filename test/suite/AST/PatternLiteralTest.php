<?php
namespace Icecave\Dialekt\AST;

use Phake;
use PHPUnit_Framework_TestCase;

class PatternLiteralTest extends PHPUnit_Framework_TestCase
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
        $visitor = Phake::mock('Icecave\Dialekt\AST\VisitorInterface');

        Phake::when($visitor)
            ->visitPatternLiteral(Phake::anyParameters())
            ->thenReturn('<visitor result>');

        $this->assertSame('<visitor result>', $this->node->accept($visitor));
    }
}
