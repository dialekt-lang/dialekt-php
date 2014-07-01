<?php
namespace Icecave\Dialekt\AST;

use Icecave\Dialekt\Parser\Token;

/**
 * A base class providing common functionality for expressions.
 */
abstract class AbstractExpression implements ExpressionInterface
{
    /**
     * Fetch the first token from the source that is part of this expression.
     *
     * @return Token|null The first token from this expression.
     */
    public function firstToken()
    {
        return $this->firstToken;
    }

    /**
     * Fetch the last token from the source that is part of this expression.
     *
     * @return Token|null The last token from this expression.
     */
    public function lastToken()
    {
        return $this->lastToken;
    }

    /**
     * Set the delimiting tokens for this expression.
     *
     * @param Token $firstToken The first token from this expression.
     * @param Token $lastToken  The last token from this expression.
     */
    public function setTokens(Token $firstToken, Token $lastToken)
    {
        $this->firstToken = $firstToken;
        $this->lastToken = $lastToken;
    }

    private $firstToken;
    private $lastToken;
}
