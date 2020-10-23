<?php
//var_dump($argv);

if (isset($argv[1])) {
  echo md5($argv[1]);
}

