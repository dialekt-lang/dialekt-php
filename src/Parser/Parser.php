<?php
namespace Icecave\Dialekt\Parser;

use Icecave\Dialekt\Expression\AbstractCompoundExpression;
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
    public function __construct(LexerInterface $lexer = null)
    {
        if (null === $lexer) {
            $lexer = new Lexer;
        }

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
        ++$this->nestingLevel;

        next($tokens);

        $expression = $this->parseExpression($tokens);

        $this->expect(
            $tokens,
            Token::CLOSE_BRACKET
        );

        next($tokens);

        --$this->nestingLevel;

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

        while ($minimumPrecedence <= ($precedence = $this->getOperatorPrecedence($tokens))) {

            $token = current($tokens);

            // Explicit logical AND ...
            if (Token::LOGICAL_AND === $token->type) {
                $operator = Token::LOGICAL_AND;
                $expressionClass = 'Icecave\Dialekt\Expression\LogicalAnd';
                next($tokens);

            // Explicit logical OR ...
            } elseif (Token::LOGICAL_OR === $token->type) {
                $operator = Token::LOGICAL_OR;
                $expressionClass = 'Icecave\Dialekt\Expression\LogicalOr';
                next($tokens);

            // Implicit logical AND ...
            } else {
                $operator = Token::LOGICAL_AND;
                $expressionClass = 'Icecave\Dialekt\Expression\LogicalAnd';
            }

            $rightExpression = $this->parseUnaryExpression($tokens);

            if ($precedence < $this->getOperatorPrecedence($tokens)) {
                $rightExpression = $this->parseCompoundExpression(
                    $tokens,
                    $rightExpression,
                    $precedence + 1
                );
            }

            if ($allowCollapse && $leftExpression instanceof $expressionClass) {
                $leftExpression->add($rightExpression);
            } else {
                $leftExpression = new $expressionClass(
                    $leftExpression,
                    $rightExpression
                );
                $allowCollapse = true;
            }
        }

        return $leftExpression;
    }

    private function getOperatorPrecedence(array &$tokens)
    {
        $token = current($tokens);

        if (false === $token) {
            return -1;
        } elseif (Token::CLOSE_BRACKET === $token->type) {
            return -1;
        } elseif (Token::LOGICAL_OR === $token->type) {
            return 0;
        } else {
            return 1;
        }

        return -1;
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
    private $nestingLevel = 0;
}
