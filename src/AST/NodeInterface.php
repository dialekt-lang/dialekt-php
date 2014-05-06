<?php
namespace Icecave\Dialekt\AST;

/**
 * Base interface for all AST nodes.
 */
interface NodeInterface
{
    /**
     * Pass this node to the appropriate method on the given visitor.
     *
     * @param VisitorInterface $visitor The visitor to dispatch to.
     *
     * @return mixed The visitation result.
     */
    public function accept(VisitorInterface $visitor);
}
