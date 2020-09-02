<?php

namespace Dialekt\Renderer;

use Dialekt\AST\ExpressionInterface;

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
