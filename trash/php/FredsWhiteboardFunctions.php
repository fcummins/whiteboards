<?php
include_once("FredsWrapperFunctions.php");
include_once("FredsFreetag2.php");

/* displayBlankEntry
 * This is the starging point.  This function displays a form into
   which you enter the Flickr URL and the photo data
*/

function displayBlankEntry()
{
?>
    <form action="enterBoardDetails.php" method="post">
        <table>
            <tr><td>HTML:</td><td>
                <input type="text" name="htmlstring" size="90" value=""></td></tr>
            <tr><td>Date:</td><td><input type="date" name="thedate"></td></tr>
            <tr><td colspan="2" align="center" ><input type="submit" value="Add Board"></td></tr></table>
    </form>
    
<?php
}

/*
   /* displayBoard is passed in an array containing board data
 */

function displayBoard($boardData)
{
    head(".","Joint Whiteboards");
?>
    
    <?php
    $url = $videoData["url"];
    $embedUrl = getYouTubeCode($url);
    ?>
    <iframe width="800" height="450" 
            src="https://www.youtube.com/embed/<?php echo $embedUrl ?>"
            frameborder="0" allowfullscreen></iframe>
    
    
    <?php
    displayEditableData($embedUrl, $videoData);
    endcontent();
    footer();
    }

    /* Flickr gives me HTML code that I need to eviscerate to get the image URL */
    /* This never worked.  This is actually my old YouTube code wrangler */
    function getFlickrCode($inhtml)
    {
        preg_match(
            '/[\\?\\&]v=([^\\?\\&]+)/',
            $inurl,
            $matches
        );
        if(count($matches)>0){
            $id = $matches[1];
        } else {
            $id = "1";
        }
        return $id;

    }

  

    function showEntryForm($jpg, $flickrurl,$thedate)
    {
        // Take tagarr and turn into string for use below
        //$tagstring = tagarray_to_string($videoData["tagarr"]);

        ?>

            <form action="enterBoard.php" method="post">
                <input type="hidden" name="jpgurl" value="<?php echo $jpg ; ?>">
                <input type="hidden" name="flickrurl" value="<?php echo $flickrurl ; ?>">
                <input type="hidden" name="thedate" value="<?php echo $thedate ; ?>">
     
        <table>
            <tr><td colspan="2">
                <a href="<?php echo $flickrurl; ?>"><img src="<?php echo $jpg ;?> " width="800" /></a> </td></tr>
            <tr width="800"><td>Transcription:</td><td>
                <textarea name="transcription" rows="10"  cols="90"> transcription here </textarea></td></tr>
            
            <tr><td>Tags:</td><td> <input type="text" name="tags" size="90" value="nothing" ></td></tr>
            <tr><td colspan="2" align="center" ><input type="submit" value="Update"></td></tr></table>
    </form>
<?php
    }

function tagarrayToString($tagarr)
{
    $string = "";
    foreach ($tagarr as $tag) {
        $string = $tag['tag'] . "  " . $string;
    }
    return $string;
}
?>


