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

class Renderer implements RendererInterface, VisitorInterface
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
        $expressions = array();

        foreach ($expression->children() as $child) {
            $expressions[] = $this->render($child);
        }

        return '(' . implode(' AND ', $expressions) . ')';
    }

    public function visitLogicalOr(LogicalOr $expression)
    {
        $expressions = array();

        foreach ($expression->children() as $child) {
            $expressions[] = $this->render($child);
        }

        return '(' . implode(' OR ', $expressions) . ')';
    }

    public function visitLogicalNot(LogicalNot $expression)
    {
        return 'NOT ' . $this->render($expression->child());
    }

    public function visitTag(Tag $expression)
    {
        return $this->escapeString($expression->name());
    }

    public function visitWildcard(Wildcard $expression)
    {
        return $this->escapeString($expression->pattern());
    }

    public function visitEmptyExpression(EmptyExpression $expression)
    {
        return 'NOT *';
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
}
