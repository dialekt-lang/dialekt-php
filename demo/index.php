<?php
require __DIR__ . '/../vendor/autoload.php';

$parser = new Icecave\Dialekt\Parser\Parser(isset($_GET['orByDefault']));
$renderer = new Icecave\Dialekt\Renderer\Renderer;
$treeRenderer = new Icecave\Dialekt\Renderer\TreeRenderer;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Dialekt Parser Demo</title>
        <style>
            body {
                font-family: Helvetica Neue, Helvetica, Arial, sans-serif;
                font-size: 16px;
                line-height: 20px;
                background: #ffffff;
                margin: 50px;
                padding: 0;
            }

            div.container {
                text-align: left;
                margin: auto;
                width: 760px;
                background: #87afef;
                padding: 1px 0px;
                border-radius: 16px;
            }

            input {
                font-size: 17px;
                border-radius: 8px;
                border: none;
                background: #efefef;
                padding: 7px;
                margin: 5px 0px;
            }

            #expr {
                width: 550px;
                margin-right: 10px;
                outline: none;
                font-family: "Andale Mono", "Menlo", "Monaco", "Courier New", monospace;
            }

            #submit {
                width: 100px;
                background: #afafaf;
            }

            section {
                margin: 20px;
                background: #ffffff;
                padding: 20px;
                border-radius: 12px;
            }

            footer {
                text-align: center;
                margin: 10px;
                font-size: 14px;
            }

            a {
                text-decoration: none;
                color: #87afef;
            }

            a:hover {
                color: #ff9804;
            }

            h1 {
                font-family: "Trebuchet MS", sans-serif;
                font-size: 24px;
                margin: 0;
                margin-bottom: 20px;
                line-height: 22px;
                padding: 0;
                border-bottom: 2px solid #cccccc;
            }

            p {
                margin: 20px 0px;
                color: #404040;
            }

            pre {
                white-space: pre-wrap;
                margin: 0;
                color: #404040;
                font-size: 15px;
                font-family: "Andale Mono", "Menlo", "Monaco", "Courier New", monospace;
            }

            .error {
                color: #ff4000;
            }
        </style>
        <script>
            window.onload = function () {
                document.getElementById('expr').focus();
            }
        </script>
    </head>
    <body>
        <div class="container">
            <section>
                <h1>Expression Parser</h1>
                <p>
                This page demonstrates how tag expressions are parsed to generate an abstract syntax tree (AST).
                The AST can be traversed to produce the desired output, for example an SQL "WHERE" clause that finds
                entries with the matching tags.
                </p>
                <p>
                Enter a list of tags separated by spaces. Optionally use the <strong>AND</strong>, <strong>OR</strong>
                and <strong>NOT</strong> keywords to perform boolean operations. Expressions grouped in brackets
                are evaluated first.
                </p>
                <p>
                By default, two adjacent tags are treated as an <strong>AND</strong> operation. This behavior can be
                changed by selecting the checkbox below.
                </p>
                <form method="get">
                    <input id="expr" type="text" value="<?=htmlentities($_GET['expr'])?>" name="expr">
                    <input id="submit" type="submit" value="Parse">
                    <label><input name="orByDefault" type="checkbox" <?=isset($_GET['orByDefault']) ? ' checked' : ''?>> Use <strong>OR</strong> operator by default.</label>
                </form>
            </section>

            <?php
            if (array_key_exists('expr', $_GET)) {
                try {

                    $expression = $parser->parse($_GET['expr']);

                    echo '<section>';
                    echo '<h1>Normalized Expression</h1>';
                    echo '<pre>' . htmlentities($renderer->render($expression)) . '</pre>';
                    echo '</section>';

                    echo '<section>';
                    echo '<h1>Syntax Tree</h1>';
                    echo '<pre>' . htmlentities($treeRenderer->render($expression)) . '</pre>';
                    echo '</section>';

                } catch (Icecave\Dialekt\Parser\Exception\ParseException $e) {

                    echo '<section class="error">';
                    echo '<h1>Parse Error</h1>';
                    echo '<pre>' . htmlentities($e->getMessage()) . '</pre>';
                    echo '</section>';
                }
            }
            ?>
        </div>
        <footer>
            Powered by <a href="https://github.com/IcecaveLabs/dialekt">Dialekt</a>
        </footer>
    </body>
</html>

