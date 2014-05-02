<?php
require __DIR__ . '/../vendor/autoload.php';
?>
<h1>Expression Input</h1>
<form method="get">
    <input style="width: 90%" type="text" value="<?=htmlentities($_GET['expr'])?>" name="expr">
    <input type="submit" />
</form>

<?php
if (array_key_exists('expr', $_GET)) {
    $parser = new Icecave\Dialekt\Parser\Parser;
    $renderer = new Icecave\Dialekt\Renderer\Renderer;
    $treeRenderer = new Icecave\Dialekt\Renderer\TreeRenderer;

    try {
        $expression = $parser->parse($_GET['expr']);
    } catch (Icecave\Dialekt\Parser\Exception\ParseException $e) {

        echo '<h1>Parse Error</h1>';
        echo '<pre>' . htmlentities($e->getMessage()) . '</pre>';
        exit;
    }

    echo '<h1>Normalized Expression</h1>';
    echo '<pre>' . htmlentities($renderer->render($expression)) . '</pre>';

    echo '<h1>Syntax Tree</h1>';
    echo '<pre>' . htmlentities($treeRenderer->render($expression)) . '</pre>';
}
?>
