<?php
namespace Icecave\Dialekt\AST;

use Phake;
use PHPUnit_Framework_TestCase;

class AbstractNodeTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->node = Phake::partialMock('Icecave\Dialekt\AST\AbstractNode');
    }

    public function testSourceFailure()
    {
        $this->setExpectedException(
            'LogicException',
            'Source has not been captured.'
        );

        $this->node->source();
    }

    public function testSourceOffsetFailure()
    {
        $this->setExpectedException(
            'LogicException',
            'Source offset has not been captured.'
        );

        $this->node->sourceOffset();
    }

    public function testHasSource()
    {
        $this->assertFalse($this->node->hasSource());

        $this->node->setSource('foobar', 12);

        $this->assertTrue($this->node->hasSource());
    }

    public function testSetSource()
    {
        $this->node->setSource('foobar', 12);

        $this->assertSame('foobar', $this->node->source());
        $this->assertSame(12, $this->node->sourceOffset());
    }
}
