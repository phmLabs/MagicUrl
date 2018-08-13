<?php

$factory = new \phmLabs\MagicUrl\MagicUrlFactory();
$factory->attachRule(new \phmLabs\MagicUrl\Rule\UrlPointerRule(new \GuzzleHttp\Client()));
$uri = $factory->resolve('pointTo:http://tests.koalamon.com/pointto.php');

var_dump((string)$uri);