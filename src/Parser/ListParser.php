<?php
namespace Icecave\Dialekt\Parser;

use Icecave\Dialekt\AST\EmptyExpression;
use Icecave\Dialekt\AST\LogicalAnd;
use Icecave\Dialekt\AST\Tag;
use Icecave\Dialekt\Parser\Exception\ParseException;

/**
 * Parses a list of tags into an AND expression.
 */
class ListParser implements ParserInterface
{
    /**
     * @param string              $wildcardString The string to use as a wildcard placeholder.
     * @param LexerInterface|null $lexer
     */
    public function __construct(
        $wildcardString = null,
        LexerInterface $lexer = null
    ) {
        if (null === $wildcardString) {
            $wildcardString = Token::WILDCARD_CHARACTER;
        }

        if (null === $lexer) {
            $lexer = new Lexer;
        }

        $this->wildcardString = $wildcardString;
        $this->lexer = $lexer;
    }

    /**
     * Parse a list of tags.
     *
     * The expression must be a space-separated list of tags. The result is
     * either EmptyExpression, a single Tag node, or a LogicalAnd node
     * containing only Tag nodes.
     *
     * @param string $expression The tag list to parse.
     *
     * @return ExpressionInterface The parsed expression.
     * @throws ParseException      if the tag list is invalid.
     */
    public function parse($expression)
    {
        $tokens = $this->lexer->lex($expression);

        $result = new EmptyExpression;

        foreach ($tokens as $token) {
            if (Token::STRING !== $token->type) {
                throw new ParseException(
                    'Unexpected ' . Token::typeDescription($token->type) . ', expected ' . Token::typeDescription(Token::STRING) . ' or end of input.'
                );
            } elseif (false !== strpos($token->value, $this->wildcardString)) {
                throw new ParseException(
                    'Unexpected wildcard string "' . $this->wildcardString . '", in tag "' . $token->value . '".'
                );
            }

            $tag = new Tag($token->value);

            if ($result instanceof EmptyExpression) {
                $result = $tag;
            } else {
                if ($result instanceof Tag) {
                    $result = new LogicalAnd($result);
                }

                $result->add($tag);
            }
        }

        return $result;
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

    private $wildcardString;
    private $lexer;
}
