<?php
include_once("../layout/layout.php");  
include_once("../resources/config.php");
?>

<?php head("..","Whiteboard project"); 

$DOM = new DOMDocument();
$DOM->loadHTML($_POST['htmlstring']);

$s = $DOM->getElementsByTagName('img');
$jpg =  $s->item(0)->getAttribute('src');

$a = $DOM->getElementsByTagName('a');
$flickrurl = $a->item(0)->getAttribute('href');

?>

<form action="enterBoardToDb.php" method="post">
    <input type="hidden" name="jpgurl" value="<?php echo $jpg ; ?>">
    <input type="hidden" name="flickrurl" value="<?php echo $flickrurl ; ?>">
    <input type="hidden" name="thedate" value="<?php echo $_POST['thedate'] ; ?>">
    
    <table>
        <tr><td colspan="2">
            <a href="<?php echo $flickrurl; ?>"><img src="<?php echo $jpg ;?> " width="800" /></a> </td></tr>
        <tr width="800"><td>Transcription:</td><td>
            <textarea name="transcription" rows="10"  cols="90"> transcription here </textarea></td></tr>
        
        <tr><td>Tags:</td><td> <input type="text" name="tags" size="90" value="nothing" ></td></tr>
        <tr><td colspan="2" align="center" ><input type="submit" value="Update"></td></tr></table>
</form>




<?php endcontent(); ?>

