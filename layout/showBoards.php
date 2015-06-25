<?php
include_once("layout.php");

// routines for displaying boards in various ways

function displayEditableBoard($boardData)
{
    head("..","Whiteboard fun");

    $tagstring = tagarray_to_string($boardData['tagarr']);

?>
    <form action="updateBoardDetails.php" method="post">
        <input type="hidden" name="ix" value="<?php echo $boardData['ix']; ?>" >
    <table width="100%" cellpadding="5" cellspacing="5">
        <tr> <td colspan="2">
            <a href="<?php echo $boardData['flickrurl']; ?>"><img src="<?php echo $boardData['jpgurl'] ;?>" /></a></td>
        </tr>
        <tr><td>Tags:</td>
            <td> <input type="text" name="tags" size="90" value="<?php echo $tagstring; ?>" ></td>
        </tr>
        <tr><td>Desc:</td>
            <td><textarea name="transcription" rows="10"  cols="90"> <?php echo $boardData['transcription']; ?> </textarea></td>
        </tr>
        <tr><td colspan="2" align="center" ><input type="submit" value="Update"></td></tr>
    </table>
    </form>
            
    <?php  
    endcontent();
    
}

function tagarray_to_string($tagarr)
{
    $string = "";
    foreach ($tagarr as $tag) {
        $string = $tag['tag'] . "  " . $string;
    }
    return $string;
}

?>
