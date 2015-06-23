<?php
include_once("../resources/config.php");
//include_once("../resources/FreetagCode.php");

$connection=mysqli_connect($config['db']['host'],
                           $config['db']['username'],
                           $config['db']['password'],
                           $config['db']['dbname']);

if (mysqli_connect_errno())
{
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}



?>

<p>This is some text </p>
