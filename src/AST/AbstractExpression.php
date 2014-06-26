<?php
namespace Icecave\Dialekt\AST;

use LogicException;

/**
 * A base class providing common functionality for expressions.
 */
abstract class AbstractExpression implements ExpressionInterface
{
    /**
     * Fetch the original source code of this expression.
     *
     * @return string The original source code of this expression.
     */
    public function source()
    {
        if (null === $this->source) {
            throw new LogicException('Source has not been captured.');
        }

        return $this->source;
    }

    /**
     * Fetch the index of the first character of this expression in the source code.
     *
     * @return integer The index of the first character of this expression in the source code.
     */
    public function sourceOffset()
    {
        if (null === $this->sourceOffset) {
            throw new LogicException('Source offset has not been captured.');
        }

        return $this->sourceOffset;
    }

    /**
     * Indiciates whether or not the expression contains information about the
     * original source of the expression.
     *
     * @return boolean True if the source/offset has been captured; otherwise, false.
     */
    public function hasSource()
    {
        return null !== $this->source;
    }

    /**
     * Set the original source code of this expression.
     *
     * @param string  $source       The original source code of this expression.
     * @param integer $sourceOffset The offset into the original source code where this code begins.
     */
    public function setSource($source, $sourceOffset)
    {
        $this->source = $source;
        $this->sourceOffset = $sourceOffset;
    }

    private $source;
    private $sourceOffset;
}
