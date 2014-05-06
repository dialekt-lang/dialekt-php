<?php
namespace Icecave\Dialekt\AST;

use Phake;
use PHPUnit_Framework_TestCase;

class PatternWildcardTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->node = new PatternWildcard;
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Icecave\Dialekt\AST\VisitorInterface');

        Phake::when($visitor)
            ->visitPatternWildcard(Phake::anyParameters())
            ->thenReturn('<visitor result>');

        $this->assertSame('<visitor result>', $this->node->accept($visitor));
    }
}
