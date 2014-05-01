<?php
namespace Icecave\Dialekt\Expression;

class TagPattern implements ExpressionInterface
{
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    public function pattern()
    {
        return $this->pattern;
    }

    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitTagPattern($this);
    }

    private $pattern;
}
