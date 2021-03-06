<?php
include_once("../resources/config.php");
include_once("../resources/FreetagCode.php");
include_once("../layout/showBoards.php");

// Set up the mysqli connection

$connection=mysqli_connect($config['db']['host'],
                           $config['db']['username'],
                           $config['db']['password'],
                           $config['db']['dbname']);

if (mysqli_connect_errno())
{
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$thetranscription = htmlspecialchars($_POST['transcription'], ENT_QUOTES);

$jpgurl = $connection->real_escape_string($_POST['jpgurl']);
$flickrurl = $connection->real_escape_string($_POST['flickrurl']);
$transcription =  $connection->real_escape_string($thetranscription);
$date = $connection->real_escape_string($_POST['thedate']);

$sql=  "INSERT INTO whiteboards (jpgurl, flickrurl, transcription, date) 
            VALUES ('$jpgurl ', '$flickrurl', 
            '$transcription', '$date')";

if (!mysqli_query($connection,$sql))
{
    die('Error: ' . mysqli_error($connection));
}


$sql = "SELECT LAST_INSERT_ID()";
$res = mysqli_query($connection,$sql);
$ix = mysqli_insert_id($connection);

// I hate to do this, but I'm not about to unwrap all the freetag code,
// So we simply close this connection and open another
mysqli_close($connection);

$options = array(
    'db_user' =>  $config['db']['username'],
    'db_pass' =>  $config['db']['password'],
    'db_host' =>  $config['db']['host'],
    'db_name' =>  $config['db']['dbname']
);

$freetag = new freetag($options);

if(isset($_POST['tags']) && trim($_POST['tags']) != "") {
    $freetag->tag_object($ix, $_POST['tags']);
    $tagarr = $freetag->get_tags_on_object($ix);
} else {
    $tagarr = NULL;
}

// All done.  Now exit and present some kind of screen

$boardData = array(
    "ix" => $ix,
    "jpgurl" => $jpgurl,
    "flickrurl" => $flickrurl,
    "date" => $date,
    "tagarr" => $tagarr,
    "transcription" => $transcription
);



displayEditableBoard($boardData);

?>

<p>This is some text </p>
