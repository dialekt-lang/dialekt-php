<?php
namespace Icecave\Dialekt\Expression;

class Tag implements ExpressionInterface
{
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function name()
    {
        return $this->name;
    }

    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitTag($this);
    }

    private $name;
}
