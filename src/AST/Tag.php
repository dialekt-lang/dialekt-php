<?php

namespace Dialekt\AST;

/**
 * An AST node that represents a literal tag expression.
 */
class Tag extends AbstractExpression implements ExpressionInterface
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
