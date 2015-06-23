<?php
include_once("php/FredsFreetag2.php");
include_once("php/FredsFunctions1.php");

$connection=mysqli_connect("localhost","root","mycroft03", "Whiteboards");
if (mysqli_connect_errno())
{
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}


$ix = $_POST['ix'];

$sql = "SELECT * FROM whiteboards WHERE ix = '$ix';";

// Retrieve the board
if (!($res = mysqli_query($connection,$sql)))
{
    die('Error: ' . mysqli_error($connection));
}

$row = $res->fetch_array();

$boardData = [
    "ix" => $row['ix'],
    "boardurl"        => $row['boardurl'],
    "mytext"          => $row['mytext'],
    "date"       => $row['date'],
];

// Now we get the tags

$options = array(
    'db_user' => 'root',
    'db_pass' => 'mycroft03',
    'db_host' => 'localhost',
    'db_name' => 'Whiteboards'
);

$freetag = new freetag($options);
$boardData["tagarr"] = $freetag->get_tags_on_object($ix);

displayBoard($ix, $boardData);

?>
