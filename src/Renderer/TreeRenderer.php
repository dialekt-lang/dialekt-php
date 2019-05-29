<?php
namespace Icecave\Dialekt\Renderer;

use Icecave\Dialekt\AST\EmptyExpression;
use Icecave\Dialekt\AST\ExpressionInterface;
use Icecave\Dialekt\AST\LogicalAnd;
use Icecave\Dialekt\AST\LogicalNot;
use Icecave\Dialekt\AST\LogicalOr;
use Icecave\Dialekt\AST\NodeInterface;
use Icecave\Dialekt\AST\Pattern;
use Icecave\Dialekt\AST\PatternLiteral;
use Icecave\Dialekt\AST\PatternWildcard;
use Icecave\Dialekt\AST\Tag;
use Icecave\Dialekt\AST\VisitorInterface;

/**
 * Render an AST expression to a string representing the tree structure.
 */
class TreeRenderer implements RendererInterface, VisitorInterface
{
    /**
     * Construct a new tree renderer,
     *
     * @param string|null $endOfLine The end-of-line string to use.
     */
    public function __construct($endOfLine = null)
    {
        if (null === $endOfLine) {
            $endOfLine = "\n";
        }

        $this->endOfLine = $endOfLine;
    }

    /**
     * Get the end-of-line string.
     *
     * @return string The end-of-line string.
     */
    public function endOfLine()
    {
        return $this->endOfLine;
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
        return 'AND' . $this->endOfLine() . $this->renderChildren($node);
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
        return 'OR' . $this->endOfLine() . $this->renderChildren($node);
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
        $child = $node->child()->accept($this);

        return 'NOT' . $this->endOfLine() . $this->indent('- ' . $child);
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
        return 'TAG ' . json_encode($node->name());
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
        return 'PATTERN' . $this->endOfLine() . $this->renderChildren($node);
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
        return 'LITERAL ' . json_encode($node->string());
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
        return 'WILDCARD';
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
        return 'EMPTY';
    }

    private function renderChildren(NodeInterface $node)
    {
        $output = '';

        foreach ($node->children() as $n) {
            $output .= $this->indent(
                '- ' . $n->accept($this)
            ) . $this->endOfLine();
        }

        return rtrim($output);
    }

    private function indent($string)
    {
        $endOfLine = $this->endOfLine();

        return '  ' . str_replace($endOfLine, $endOfLine . '  ', $string);
    }

    private $endOfLine;
}
