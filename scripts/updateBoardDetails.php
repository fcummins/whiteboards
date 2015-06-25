<?php
include_once("../resources/config.php");
include_once("../resources/FreetagCode.php");
include_once("../layout/showBoards.php");
include_once("../layout/layout.php");

$connection=mysqli_connect($config['db']['host'],
                           $config['db']['username'],
                           $config['db']['password'],
                           $config['db']['dbname']);

if (mysqli_connect_errno())
{
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$thetranscription = htmlspecialchars($_POST['transcription'], ENT_QUOTES);
$ix = $_POST['ix'];

$sql = "UPDATE whiteboards
        SET transcription='$thetranscription'
        WHERE ix='$ix';";

if (!mysqli_query($connection,$sql))
{
    die('Error: ' . mysqli_error($connection));
}

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

head("..", "Whiteboard fun");
    ?>
<h1>DONE!  NOW WHAT?</h1>

<p>Tags are: <?php echo $_POST['tags']; ?> </p>

<?php

endcontent();

?>

