<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "lib/jBBcode/Parser.php");

$parser = new JBBCode\Parser();

$parser->addBBCode("quote", '<div class="quote">{param}</div>');
$parser->addBBCode("code", '<pre class="code">{param}</pre>', false, false, 1);
