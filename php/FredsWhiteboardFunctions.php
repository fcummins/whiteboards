<?php
include_once("php/wrapperfuncs.php");
include_once("php/FredsFreetag2.php");

/* functions defined by me
 *
 */

/* displayTiles
 * generate a page with multiple boards
 */

function displayTiles($result, $msg = NULL)
{
    head(".","Whiteboards");
?>
    <H2> Search Results </H2>
    

<?php

if($msg)
{
    Echo "<p>Message: " . $msg . "</p>";
}

if($result) {
?> <form action="getboard.php"  method="post"> <?php
          while($row = $result->fetch_array())
          {
              displayOneTile($row['ix'], $row['mytext'], $row['date'], $row['boardurl']);
          }
?> </form> <?php
              
}
endcontent();
footer();

}

function displayOneTile($ix, $mytext, $date, $boardurl)
{
    $id = getFlickrCode($boardurl);
?>
    <div id="tile">
        <table cellpadding="5" width="100%">
                         <tr><td colspan="2" ><?php echo $title; ?></td></tr>

            <tr>
                <td align="left">
                    <iframe width="300" height="169" 
                            src="https://www.youtube.com/embed/<?php echo $id ?>"
                            frameborder="1" allowfullscreen></iframe>
                </td>
                <td>
                    <input type="radio" name="ix" value="<?php echo $ix; ?>">
                    <p>Index: <?php echo $ix; ?></p>
                    <p><input type="submit" value="Show"></p>
                </td>
            </tr>
            <tr><td colspan="2" align="left"><?php echo $descr; ?> </td></tr>
        </table>

      </div>
<?php

}

/*
   /* displayVideo is passed in an array containing video data
 */

function displayVideo($ix, $videoData)
{
    head(".","Joint Speech Archive");
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

    function displayBlankEntry()
    {
    ?>
     <form action="addboard.php" method="post">
        <table>
            <tr><td width="80" >
                URL:</td><td colspan="5">
                <input type="text" name="url" size="90" value=""></td></tr>
            <tr><td>Date:</td><td><input type="date" name="date" colspan="5"></td></tr>
            <tr><td>Text:</td><td colspan="5">
                <textarea name="mytext" rows="10"  cols="90">Enter transcription here </textarea></td></tr>
            <tr><td>Tags:</td><td colspan="5"> <input type="text" name="tags" size="90" value="" ></td></tr>
            <tr><td colspan="6" align="center" ><input type="submit" value="Add Board"></td></tr></table>
    </form>

        <?php
    }

    function displayEditableData($embedUrl, $videoData)
    {
        // Take tagarr and turn into string for use below
        $tagstring = tagarray_to_string($videoData["tagarr"]);

        ?>

            <h2>Video data</h2>
    
    <form action="editvideo.php" method="post">
        <input type="hidden" name="ix" value="<?php echo $videoData["ix"]; ?>">
        <input type="hidden"  name="url"  value="<?php echo $videoData["url"]; ?>">
        <table>
            <tr><td width="80" >
                Video Title:</td><td colspan="5">
                <input type="text" name="title" size="90" value="<?php echo $videoData["title"]; ?>"></td></tr>
            <tr><td>URL: </td><td colspan="3"><?php echo $videoData["url"]; ?>&nbsp;</td>
                <td>Index: </td><td><?php echo $videoData["ix"]; ?></td></tr>
            <tr><td>Domain:</td><td> <select name="domain">
                <option value="protest" <?php if($videoData["domain"] == "protest") { echo 'selected';}?> > Protest </option>
                <option value="prayer" <?php if($videoData["domain"] == "prayer") { echo 'selected';}?> >Prayer</option>
                <option value="sports" <?php if($videoData["domain"] == "sports") { echo 'selected';}?> >Sports</option>
                <option value="secular" <?php if($videoData["domain"] == "secular") { echo 'selected';}?> >Secular</option>
                <option value="education" <?php if($videoData["domain"] == "education") { echo 'selected';}?>>Education</option>
                <option value="performance" <?php if($videoData["domain"] == "performance") { echo 'selected';}?>>Performance</option>
                <option value="misc" <?php if($videoData["domain"] == "misc") { echo 'selected';}?>>Miscellaneous</option>
            </select></td>
            <td>Structure:</td><td> <select name="structure" >
                <option value="none"<?php if($videoData["structure"] == "none") { echo 'selected';}?> >No value</option>
                <option value="call and response"<?php if($videoData["structure"] == "call and response") { echo 'selected';}?> >Call and response</option>
                <option value="mirroring" <?php if($videoData["structure"] == "mirroring") { echo 'selected';}?>>Mirroring</option>
                <option value="chorusing" <?php if($videoData["structure"] == "chorusing") { echo 'selected';}?>>Chorusing</option>
                <option value="assent" <?php if($videoData["structure"] == "assent") { echo 'selected';}?>>Assent/amen/option>
                <option value="complex"<?php if($videoData["structure"] == "complex") { echo 'selected';}?> >Complex</option>
            </select></td>
            <td>Movement:</td><td> <select name="movement" >
                <option value="none"<?php if($videoData["movement"] == "none") { echo 'selected';}?> >None</option>
                <option value="fist pump"<?php if($videoData["movement"] == "fist pump") { echo 'selected';}?> >Fist pump</option>
                <option value="clapping" <?php if($videoData["movement"] == "clapping") { echo 'selected';}?>>Clapping</option>
                <option value="noteworthy" <?php if($videoData["movement"] == "noteworthy") { echo 'selected';}?>>Noteworthy</option>
                <option value="ritualised" <?php if($videoData["movement"] == "ritualised") { echo 'selected';}?>>Ritualised</option>
            </select></td></tr>
            <tr><td>Prosody:</td><td> <select name="prosody" >
                <option value="spoken" <?php if($videoData["prosody"] == "spoken") { echo 'selected';}?> >Spoken</option>
                <option value="sung"  <?php if($videoData["prosody"] == "sung") { echo 'selected';}?> >Sung</option>
            </select></td>
            <td>&nbsp;</td>                    <td>&nbsp;</td>
            <td>&nbsp;</td>                    <td>&nbsp;</td></tr>
            
            <tr><td>Description:</td><td colspan="5">
                <textarea name="description" rows="10"  cols="90"> <?php echo $videoData["description"]; ?> </textarea></td></tr>
            <tr><td>Tags:</td><td colspan="5"> <input type="text" name="tags" size="90" value="<?php echo $tagstring; ?>" ></td></tr>
            <tr><td colspan="6" align="center" ><input type="submit" value="Update"></td></tr></table>
    </form>
<?php
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


