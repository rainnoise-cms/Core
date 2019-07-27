<?php
/**
 * @var Core $this
 */
use App\Core;?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link href="<?= $this->resPath() ?>/core.css" rel="stylesheet"/>
    <title>Exception</title>
</head>
<body>
<h1>AHAHAHAHHA!!!!!</h1>
<H3>EXCEPTION!</H3>
<pre><?= print_r($this->data, true) ?></pre>
</body>
</html>