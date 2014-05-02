<?php
namespace Icecave\Dialekt\Expression;

use Phake;
use PHPUnit_Framework_TestCase;

class WildcardTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->expression = new Wildcard('foo*');
    }

    public function testPattern()
    {
        $this->assertSame('foo*', $this->expression->pattern());
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Icecave\Dialekt\Expression\VisitorInterface');

        Phake::when($visitor)
            ->visitWildcard(Phake::anyParameters())
            ->thenReturn('<visitor result>');

        $this->assertSame('<visitor result>', $this->expression->accept($visitor));
    }
}
