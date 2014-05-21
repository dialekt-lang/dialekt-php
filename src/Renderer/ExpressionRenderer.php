<?php
namespace Icecave\Dialekt\Renderer;

use Icecave\Dialekt\AST\EmptyExpression;
use Icecave\Dialekt\AST\ExpressionInterface;
use Icecave\Dialekt\AST\LogicalAnd;
use Icecave\Dialekt\AST\LogicalNot;
use Icecave\Dialekt\AST\LogicalOr;
use Icecave\Dialekt\AST\Pattern;
use Icecave\Dialekt\AST\PatternLiteral;
use Icecave\Dialekt\AST\PatternWildcard;
use Icecave\Dialekt\AST\Tag;
use Icecave\Dialekt\AST\VisitorInterface;
use Icecave\Dialekt\Parser\Parser;
use Icecave\Dialekt\Parser\Token;
use Icecave\Dialekt\Renderer\Exception\RenderException;

/**
 * Renders an AST expression to an expression string.
 */
class ExpressionRenderer implements RendererInterface, VisitorInterface
{
    /**
     * @param string $wildcardString The string to use as a wildcard placeholder.
     */
    public function __construct($wildcardString = null)
    {
        if (null === $wildcardString) {
            $wildcardString = Token::WILDCARD_CHARACTER;
        }

        $this->wildcardString = $wildcardString;
    }

    /**
     * Render an expression to a string.
     *
     * @param ExpressionInterface $expression The expression to render.
     *
     * @return string The rendered expression.
     */
    public function render(ExpressionInterface $expression)
    {
        return $expression->accept($this);
    }

    /**
     * Visit a LogicalAnd node.
     *
     * @internal
     *
     * @param LogicalAnd $node The node to visit.
     *
     * @return mixed
     */
    public function visitLogicalAnd(LogicalAnd $node)
    {
        $expressions = array();

        foreach ($node->children() as $n) {
            $expressions[] = $n->accept($this);
        }

        return '(' . implode(' AND ', $expressions) . ')';
    }

    /**
     * Visit a LogicalOr node.
     *
     * @internal
     *
     * @param LogicalOr $node The node to visit.
     *
     * @return mixed
     */
    public function visitLogicalOr(LogicalOr $node)
    {
        $expressions = array();

        foreach ($node->children() as $n) {
            $expressions[] = $n->accept($this);
        }

        return '(' . implode(' OR ', $expressions) . ')';
    }

    /**
     * Visit a LogicalNot node.
     *
     * @internal
     *
     * @param LogicalNot $node The node to visit.
     *
     * @return mixed
     */
    public function visitLogicalNot(LogicalNot $node)
    {
        return 'NOT ' . $node->child()->accept($this);
    }

    /**
     * Visit a Tag node.
     *
     * @internal
     *
     * @param Tag $node The node to visit.
     *
     * @return mixed
     */
    public function visitTag(Tag $node)
    {
        return $this->escapeString($node->name());
    }

    /**
     * Visit a Pattern node.
     *
     * @internal
     *
     * @param Pattern $node The node to visit.
     *
     * @return mixed
     */
    public function visitPattern(Pattern $node)
    {
        $string = '';

        foreach ($node->children() as $n) {
            $string .= $n->accept($this);
        }

        return $this->escapeString($string);
    }

    /**
     * Visit a PatternLiteral node.
     *
     * @internal
     *
     * @param PatternLiteral $node The node to visit.
     *
     * @return mixed
     */
    public function visitPatternLiteral(PatternLiteral $node)
    {
        if (false === strpos($node->string(), $this->wildcardString)) {
            return $node->string();
        }

        throw new RenderException(
            sprintf(
                'The pattern literal "%s" contains the wildcard string "%s".',
                $node->string(),
                $this->wildcardString
            )
        );
    }

    /**
     * Visit a PatternWildcard node.
     *
     * @internal
     *
     * @param PatternWildcard $node The node to visit.
     *
     * @return mixed
     */
    public function visitPatternWildcard(PatternWildcard $node)
    {
        return $this->wildcardString;
    }

    /**
     * Visit a EmptyExpression node.
     *
     * @internal
     *
     * @param EmptyExpression $node The node to visit.
     *
     * @return mixed
     */
    public function visitEmptyExpression(EmptyExpression $node)
    {
        return 'NOT ' . $this->wildcardString;
    }

    private function escapeString($string)
    {
        if (
            0 === strcasecmp('and', $string)
            || 0 === strcasecmp('or', $string)
            || 0 === strcasecmp('not', $string)
        ) {
            return '"' . $string . '"';
        }

        $count = 0;
        $string = preg_replace(
            '/[\(\)"\\\\]/',
            '\\\\$0',
            $string,
            -1,
            $count
        );

        if ($count || preg_match('/\s/', $string)) {
            return '"' . $string . '"';
        }

        return $string;
    }

    private $wildcardString;
}
