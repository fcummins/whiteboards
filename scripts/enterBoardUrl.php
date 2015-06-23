<?php
include_once("../layout/layout.php");  
include_once("../resources/config.php");
?>


<?php head("..","Whiteboard project"); ?>

<h1>Add board url</h1>

<form action="enterBoardDetails.php" method="post">
    <table>
        <tr><td>HTML:</td><td>
            <input type="text" name="htmlstring" size="90" value="" onpaste="null"></td></tr>
        <tr><td>Date:</td><td><input type="date" name="thedate"></td></tr>
        <tr><td colspan="2" align="center" ><input type="submit" value="Add Board"></td></tr></table>
</form>
    
<?php endcontent(); ?>

