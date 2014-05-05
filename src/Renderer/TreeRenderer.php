<?php
namespace Icecave\Dialekt\Renderer;

use Icecave\Dialekt\Expression\EmptyExpression;
use Icecave\Dialekt\Expression\ExpressionInterface;
use Icecave\Dialekt\Expression\LogicalAnd;
use Icecave\Dialekt\Expression\LogicalNot;
use Icecave\Dialekt\Expression\LogicalOr;
use Icecave\Dialekt\Expression\Tag;
use Icecave\Dialekt\Expression\VisitorInterface;
use Icecave\Dialekt\Expression\Wildcard;

class TreeRenderer implements RendererInterface, VisitorInterface
{
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

    public function visitLogicalAnd(LogicalAnd $expression)
    {
        $output  = 'AND' . PHP_EOL;

        foreach ($expression->children() as $child) {
            $output .= $this->indent(
                '- ' . $this->render($child)
            ) . PHP_EOL;
        }

        return rtrim($output);
    }

    public function visitLogicalOr(LogicalOr $expression)
    {
        $output  = 'OR' . PHP_EOL;

        foreach ($expression->children() as $child) {
            $output .= $this->indent(
                '- ' . $this->render($child)
            ) . PHP_EOL;
        }

        return rtrim($output);
    }

    public function visitLogicalNot(LogicalNot $expression)
    {
        $child = $this->render($expression->child());

        $output  = 'NOT ' . PHP_EOL;
        $output .= $this->indent('- ' . $child);

        return $output;
    }

    public function visitTag(Tag $expression)
    {
        return 'TAG ' . json_encode($expression->name());
    }

    public function visitWildcard(Wildcard $expression)
    {
        return 'WILDCARD ' . json_encode($expression->name());
    }

    public function visitEmptyExpression(EmptyExpression $expression)
    {
        return 'EMPTY';
    }

    private function indent($string)
    {
        return '  ' . str_replace(PHP_EOL, PHP_EOL . '  ', $string);
    }

    private $indentLevel = 0;
}
