<?php

$pattern = '^(http|https)://^';
$url = 'ftp://www.test.com';
var_dump(preg_match($pattern,$url));
