<?php
namespace Icecave\Dialekt\Parser;

use Icecave\Dialekt\Expression\EmptyExpression;
use Icecave\Dialekt\Expression\ExpressionInterface;
use Icecave\Dialekt\Expression\LogicalAnd;
use Icecave\Dialekt\Expression\LogicalNot;
use Icecave\Dialekt\Expression\LogicalOr;
use Icecave\Dialekt\Expression\Tag;
use Icecave\Dialekt\Expression\Wildcard;
use Icecave\Dialekt\Parser\Exception\ParseException;

class Parser implements ParserInterface
{
    public function __construct(
        $logicalOrByDefault = false,
        LexerInterface $lexer = null
    ) {
        if (null === $lexer) {
            $lexer = new Lexer;
        }

        $this->logicalOrByDefault = $logicalOrByDefault;
        $this->lexer = $lexer;
    }

    /**
     * Parse an expression.
     *
     * @param string $expression The expression to parse.
     *
     * @return ExpressionInterface The parsed expression.
     * @throws ParseException      if the expression is invalid.
     */
    public function parse($expression)
    {
        $tokens = $this->lexer->lex($expression);

        if ($tokens) {
            $expression = $this->parseExpression($tokens);

            if (null !== key($tokens)) {
                throw new ParseException(
                    'Unexpected ' . Token::typeDescription(current($tokens)->type) . ', expected end of input.'
                );
            }

            return $expression;
        }

        return new EmptyExpression;
    }

    private function parseExpression(array &$tokens)
    {
        $expression = $this->parseUnaryExpression($tokens);
        $expression = $this->parseCompoundExpression($tokens, $expression);

        return $expression;
    }

    private function parseUnaryExpression(array &$tokens)
    {
        $token = $this->expect(
            $tokens,
            Token::STRING,
            Token::LOGICAL_NOT,
            Token::OPEN_BRACKET
        );

        if (Token::LOGICAL_NOT === $token->type) {
            return $this->parseLogicalNot($tokens);
        } elseif (Token::OPEN_BRACKET === $token->type) {
            return $this->parseNestedExpression($tokens);
        } elseif (false === strpos($token->value, '*')) {
            return $this->parseTag($tokens);
        } else {
            return $this->parseWildcard($tokens);
        }
    }

    private function parseTag(array &$tokens)
    {
        $token = current($tokens);

        if (!preg_match('/^[a-z\d]+(-[a-z\d]+)*$/i', $token->value)) {
            throw new ParseException('Invalid tag: "' . $token->value . '".');
        }

        next($tokens);

        return new Tag($token->value);
    }

    private function parseWildcard(array &$tokens)
    {
        $token = current($tokens);

        if (!preg_match('/^[a-z0-9\*]+(-[a-z0-9\*]+)*$/i', $token->value)) {
            throw new ParseException('Invalid wildcard: "' . $token->value . '".');
        }

        next($tokens);

        return new Wildcard($token->value);
    }

    private function parseNestedExpression(array &$tokens)
    {
        next($tokens);

        $expression = $this->parseExpression($tokens);

        $this->expect(
            $tokens,
            Token::CLOSE_BRACKET
        );

        next($tokens);

        return $expression;
    }

    private function parseLogicalNot(array &$tokens)
    {
        next($tokens);

        return new LogicalNot(
            $this->parseUnaryExpression($tokens)
        );
    }

    private function parseCompoundExpression(array &$tokens, ExpressionInterface $leftExpression, $minimumPrecedence = 0)
    {
        $allowCollapse = false;

        while (true) {

            // Parse the operator and determine whether or not it's explicit ...
            list($operator, $isExplicit) = $this->parseOperator($tokens);

            $precedence = $this->operatorPrecedence[$operator];

            // Abort if the operator's precedence is less than what we're looking for ...
            if ($precedence < $minimumPrecedence) {
                break;
            }

            // Advance the token pointer if an explicit operator token was found ...
            if ($isExplicit) {
                next($tokens);
            }

            // Parse the expression to the right of the operator ...
            $rightExpression = $this->parseUnaryExpression($tokens);

            // Only parse additional compound expressions if their precedence is greater than the
            // expression already being parsed ...
            list($nextOperator) = $this->parseOperator($tokens);

            if ($precedence < $this->operatorPrecedence[$nextOperator]) {
                $rightExpression = $this->parseCompoundExpression(
                    $tokens,
                    $rightExpression,
                    $precedence + 1
                );
            }

            // Combine the parsed expression with the existing expression ...
            $operatorClass = $this->operatorClasses[$operator];

            // Collapse the expression into an existing expression of the same type ...
            if ($allowCollapse && $leftExpression instanceof $operatorClass) {
                $leftExpression->add($rightExpression);
            } else {
                $leftExpression = new $operatorClass(
                    $leftExpression,
                    $rightExpression
                );
                $allowCollapse = true;
            }
        }

        return $leftExpression;
    }

    private function parseOperator(array &$tokens)
    {
        $token = current($tokens);

        // End of input ...
        if (false === $token) {
            return array(null, false);

        // Closing bracket ...
        } elseif (Token::CLOSE_BRACKET === $token->type) {
            return array(null, false);

        // Explicit logical OR ...
        } elseif (Token::LOGICAL_OR === $token->type) {
            return array(Token::LOGICAL_OR, true);

        // Explicit logical AND ...
        } elseif (Token::LOGICAL_AND === $token->type) {
            return array(Token::LOGICAL_AND, true);

        // Implicit logical OR ...
        } elseif ($this->logicalOrByDefault) {
            return array(Token::LOGICAL_OR, false);

        // Implicit logical AND ...
        } else {
            return array(Token::LOGICAL_AND, false);
        }

        return array(null, false);
    }

    private function expect(array &$tokens)
    {
        $types = array_slice(func_get_args(), 1);
        $token = current($tokens);

        if (!$token) {
            throw new ParseException(
                'Unexpected end of input, expected ' . $this->formatExpectedTokenNames($types) . '.'
            );
        } elseif (!in_array($token->type, $types)) {
            throw new ParseException(
                'Unexpected ' . Token::typeDescription($token->type) . ', expected ' . $this->formatExpectedTokenNames($types) . '.'
            );
        }

        return $token;
    }

    private function formatExpectedTokenNames(array $types)
    {
        $types = array_map(
            'Icecave\Dialekt\Parser\Token::typeDescription',
            $types
        );

        if (count($types) === 1) {
            return $types[0];
        }

        $lastType = array_pop($types);

        return implode(', ', $types) . ' or ' . $lastType;
    }

    private $lexer;
    private $logicalOrByDefault;

    private $operatorClasses = array(
        Token::LOGICAL_AND => 'Icecave\Dialekt\Expression\LogicalAnd',
        Token::LOGICAL_OR  => 'Icecave\Dialekt\Expression\LogicalOr',
    );

    private $operatorPrecedence = array(
        Token::LOGICAL_AND => 1,
        Token::LOGICAL_OR  => 0,
        null               => -1,
    );
}
