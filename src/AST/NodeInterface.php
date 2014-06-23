<?php
namespace Icecave\Dialekt\AST;

use LogicException;

/**
 * An AST node.
 */
interface NodeInterface
{
    /**
     * Fetch the original source code of this node.
     *
     * @return string         The original source code of this node.
     * @throws LogicException if the source has not been captured by the parser.
     */
    public function source();

    /**
     * Fetch the index of the first character of this node in the source code.
     *
     * @return integer        The index of the first character of this node in the source code.
     * @throws LogicException if the source has not been captured by the parser.
     */
    public function offset();

    /**
     * Indiciates whether or not the node contains information about the
     * original source of the node.
     *
     * @return boolean True if the source/offset has been captured; otherwise, false.
     */
    public function hasSource();

    /**
     * Pass this node to the appropriate method on the given visitor.
     *
     * @param VisitorInterface $visitor The visitor to dispatch to.
     *
     * @return mixed The visitation result.
     */
    public function accept(VisitorInterface $visitor);
}
