<?php
echo "Testing 127.0.0.1:3307...\n";
$c1 = @mysqli_connect('127.0.0.1', 'root', '', 'myhmsdb', 3307);
var_dump($c1 ? "Success!" : mysqli_connect_error());

echo "Testing localhost:3307...\n";
$c2 = @mysqli_connect('localhost', 'root', '', 'myhmsdb', 3307);
var_dump($c2 ? "Success!" : mysqli_connect_error());

echo "Testing 127.0.0.1:3307...\n";
$c3 = @mysqli_connect('127.0.0.1', 'root', '', 'myhmsdb', 3307);
var_dump($c3 ? "Success!" : mysqli_connect_error());

echo "Testing localhost:3307...\n";
$c4 = @mysqli_connect('localhost', 'root', '', 'myhmsdb', 3307);
var_dump($c4 ? "Success!" : mysqli_connect_error());
