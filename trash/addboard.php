<?php
include_once("php/FredsFreetag2.php");
include_once("php/FredsFunctions1.php");

// Hi Fred!

$connection=mysqli_connect("localhost","root","mycroft03", "Whiteboards");
if (mysqli_connect_errno())
{
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

// replace quotes by HTML escape sequences
$mytext = htmlspecialchars($_POST['mytext'], ENT_QUOTES);
$url = getFlickrCode($_POST['url']);


// Get the data from the HTML form
$sql="INSERT INTO videos (title, url, domain, structure, movement, prosody, description)
          VALUES ('$thetitle','$theurl','$_POST[domain]','$_POST[structure]',
          '$_POST[movement]','$_POST[prosody]','$thedesc')";

// Create the new entry in the database
if (!mysqli_query($connection,$sql))
{
    die('Error: ' . mysqli_error($connection));
}

// Now we need to get the Primary Key of the record we just created

$sql = "SELECT LAST_INSERT_ID()";
$res = mysqli_query($connection,$sql);
$ix = mysqli_insert_id($connection);

// I hate to do this, but I'm not about to unwrap all the freetag code,
// So we simply close this connection and open another
mysqli_close($connection);

// Now we add the tags

$options = array(
    'db_user' => 'root',
    'db_pass' => 'mycroft03',
    'db_host' => 'localhost',
    'db_name' => 'JS2'
);

$freetag = new freetag($options);

if(isset($_POST['tags']) && trim($_POST['tags']) != "") {
    $freetag->tag_object($ix, $_POST['tags']);
    $tagarr = $freetag->get_tags_on_object($ix);
} else {
    $tagarr = NULL;
}

$videoData = [
    "ix" => $ix,
    "title"        => $_POST['title'],
    "url"          => $theurl,
    "domain"       => $_POST['domain'],
    "structure"    => $_POST['structure'],
    "movement"     => $_POST['movement'],
    "prosody"      => $_POST['prosody'],
    "description"  => $_POST['description'],
    "tagarr"       => $tagarr
];

displayVideo($ix, $videoData);

?>

