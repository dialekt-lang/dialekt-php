<?php
namespace Icecave\Dialekt\AST;

/**
 * Represents a literal tag spring in an expression.
 */
class Tag implements ExpressionInterface
{
    /**
     * @param string The tag name.
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Fetch the tag name.
     *
     * @return string The tag name.
     */
    public function name()
    {
        return $this->name;
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
        return $visitor->visitTag($this);
    }

    private $name;
}
