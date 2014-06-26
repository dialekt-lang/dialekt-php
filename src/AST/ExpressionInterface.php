<?php
namespace Icecave\Dialekt\AST;

/**
 * An AST node that is an expression.
 *
 * Not all nodes in the tree represent an entire (sub-)expression.
 */
interface ExpressionInterface extends NodeInterface
{
    /**
     * Fetch the original source code of this expression.
     *
     * @return string         The original source code of this expression.
     * @throws LogicException if the source has not been captured by the parser.
     */
    public function source();

    /**
     * Fetch the index of the first character of this expression in the source code.
     *
     * @return integer        The index of the first character of this expression in the source code.
     * @throws LogicException if the source has not been captured by the parser.
     */
    public function sourceOffset();

    /**
     * Indiciates whether or not the expression contains information about the
     * original source of the expression.
     *
     * @return boolean True if the source/offset has been captured; otherwise, false.
     */
    public function hasSource();
}
