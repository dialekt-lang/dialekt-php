<?php
namespace Icecave\Dialekt\AST;

use LogicException;

/**
 * A base class providing common functionality for nodes.
 */
abstract class AbstractNode implements NodeInterface
{
    /**
     * Fetch the original source code of this node.
     *
     * @return string The original source code of this node.
     */
    public function source()
    {
        if (null === $this->source) {
            throw new LogicException('Source has not been captured.');
        }

        return $this->source;
    }

    /**
     * Fetch the index of the first character of this node in the source code.
     *
     * @return integer The index of the first character of this node in the source code.
     */
    public function offset()
    {
        if (null === $this->offset) {
            throw new LogicException('Source offset has not been captured.');
        }

        return $this->offset;
    }

    /**
     * Indiciates whether or not the node contains information about the
     * original source of the node.
     *
     * @return boolean True if the source/offset has been captured; otherwise, false.
     */
    public function hasSource()
    {
        return null !== $this->source;
    }

    private $offset;
    private $source;
}
