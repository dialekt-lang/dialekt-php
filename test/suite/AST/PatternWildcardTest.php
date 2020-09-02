<?php
namespace Dialekt\AST;

use Phake;
use PHPUnit\Framework\TestCase;

class PatternWildcardTest extends TestCase
{
    public function setUp(): void
    {
        $this->node = new PatternWildcard();
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Dialekt\AST\VisitorInterface');

        Phake::when($visitor)
            ->visitPatternWildcard(Phake::anyParameters())
            ->thenReturn('<visitor result>');

        $this->assertSame('<visitor result>', $this->node->accept($visitor));
    }
}
