<?php

$status = 1;

switch ($status) {
    case 1:
        print "one" . PHP_EOL;
        $status = 2;
    case 2:
        print "two" . PHP_EOL;
    case 3:
        print "three" . PHP_EOL;
}
