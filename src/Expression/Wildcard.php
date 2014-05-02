<?php
namespace Icecave\Dialekt\Expression;

class Wildcard implements ExpressionInterface
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
        return $visitor->visitWildcard($this);
    }

    private $pattern;
}
