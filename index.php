<?php
include_once("layout/layout.php");  
include_once("resources/config.php");
?>

<?php newHead(".","Whiteboard Project"); ?>

<img src="images/fred.gif" width="60px" >

<form>
    <input type="button" value="Add" name="addbutton" onClick="scripts/enterBoardUrl.php" >
</form>

<?php endcontent(); ?>

