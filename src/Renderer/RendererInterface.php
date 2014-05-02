<?php
namespace Icecave\Dialekt\Renderer;

use Icecave\Dialekt\Expression\ExpressionInterface;

interface RendererInterface
{
    /**
     * Render an expression to a string.
     *
     * @param ExpressionInterface $expression The expression to render.
     *
     * @return string The rendered expression.
     */
    public function render(ExpressionInterface $expression);
}
