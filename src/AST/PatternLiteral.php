<?php
namespace Icecave\Dialekt\AST;

/**
 * Represents a literal (exact-match) portion of a pattern expression.
 */
class PatternLiteral implements PatternChildInterface
{
    /**
     * @param string $string The string to match.
     */
    public function __construct($string)
    {
        $this->string = $string;
    }

    /**
     * Fetch the string to be matched.
     *
     * @return string The string to match.
     */
    public function string()
    {
        return $this->string;
    }

    /**
     * Pass this node to the appropriate method on the given visitor.
     *
     * @param VisitorInterface $visitor The visitor to dispatch to.
     *
     * @return mixed The visitation result.
     */
    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitPatternLiteral($this);
    }

    private $string;
}
