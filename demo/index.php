<?php
require __DIR__ . '/../vendor/autoload.php';
?>
<h1>Expression Input</h1>
<form method="get">
    <textarea rows="10" cols="100" name="expr"><?=htmlentities($_GET['expr'])?></textarea><br />
    <input type="submit" />
</form>

<?php
if (array_key_exists('expr', $_GET)) {
    $parser = new Icecave\Dialekt\Parser\Parser;
    $renderer = new Icecave\Dialekt\Renderer\Renderer;

    try {
        $expression = $parser->parse($_GET['expr']);
    } catch (Icecave\Dialekt\Parser\Exception\ParseException $e) {

        echo '<h1>Parse Error</h1>';
        echo '<pre>' . htmlentities($e->getMessage()) . '</pre>';
        exit;
    }

    echo '<h1>Rendered Expression</h1>';
    echo '<pre>' . htmlentities($renderer->render($expression)) . '</pre>';

    echo '<h1>Expression Object</h1>';
    echo '<pre>';
    print_r($expression);
    echo '</pre>';
}
?>
