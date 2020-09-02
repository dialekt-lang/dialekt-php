<?php

namespace Dialekt\AST;

use Phake;
use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    public function setUp(): void
    {
        $this->expression = new Tag('foo');
    }

    public function testName()
    {
        $this->assertSame('foo', $this->expression->name());
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Dialekt\AST\VisitorInterface');

        Phake::when($visitor)
            ->visitTag(Phake::anyParameters())
            ->thenReturn('<visitor result>');

        $this->assertSame('<visitor result>', $this->expression->accept($visitor));
    }
}
