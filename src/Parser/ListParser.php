<?php
namespace Icecave\Dialekt\Parser;

use Icecave\Dialekt\AST\EmptyExpression;
use Icecave\Dialekt\AST\LogicalAnd;
use Icecave\Dialekt\AST\Tag;
use Icecave\Dialekt\Parser\Exception\ParseException;

/**
 * Parses a list of tags.
 *
 * The expression must be a space-separated list of tags. The result is
 * either EmptyExpression, a single Tag node, or a LogicalAnd node
 * containing only Tag nodes.
 */
class ListParser extends AbstractParser
{
    protected function parseExpression()
    {
        $this->startExpression();

        $expression = null;

        while (current($this->tokens)) {

            $token = $this->expectToken(Token::STRING);

            if (strpos($token->value, $this->wildcardString()) !== false) {
                throw new ParseException(
                    'Unexpected wildcard string "' . $this->wildcardString() . '", in tag "' . $token->value . '".'
                );
            }

            $this->startExpression();

            next($this->tokens);

            $tag = new Tag($token->value);

            $this->endExpression($tag);

            if ($expression) {
                if ($expression instanceof Tag) {
                    $expression = new LogicalAnd($expression);
                }
                $expression->add($tag);
            } else {
                $expression = $tag;
            }
        }

        return $this->endExpression($expression);
    }

    /**
     * Parse a list of tags into an array.
     *
     * The expression must be a space-separated list of tags. The result is
     * an array of strings.
     *
     * @param string $expression The tag list to parse.
     *
     * @return array<string>  The tags in the list.
     * @throws ParseException if the tag list is invalid.
     */
    public function parseAsArray($expression)
    {
        $result = $this->parse($expression);

        if ($result instanceof EmptyExpression) {
            return array();
        } elseif ($result instanceof Tag) {
            return array($result->name());
        }

        $tags = array();

        foreach ($result->children() as $node) {
            $tags[] = $node->name();
        }

        return $tags;
    }
}
