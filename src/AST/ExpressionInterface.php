<?php
namespace Dialekt\AST;

use Dialekt\Parser\Token;

/**
 * An AST node that is an expression.
 *
 * Not all nodes in the tree represent an entire (sub-)expression.
 */
interface ExpressionInterface extends NodeInterface
{
    /**
     * Fetch the first token from the source that is part of this expression.
     *
     * @return Token|null The first token from this expression.
     */
    public function firstToken();

    /**
     * Fetch the last token from the source that is part of this expression.
     *
     * @return Token|null The last token from this expression.
     */
    public function lastToken();

    /**
     * Set the delimiting tokens for this expression.
     *
     * @param Token $firstToken The first token from this expression.
     * @param Token $lastToken  The last token from this expression.
     */
    public function setTokens(Token $firstToken, Token $lastToken);
}
