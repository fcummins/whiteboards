<?php

// Contents:
//    head($top, $runninghead)
//    endcontent()
//    footer()


function head($top,$runninghead)
{
  echo '<?xml version="1.0" encoding="iso-8859-15"?>' ;

  ?>

  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en_GB">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15" />
    <link rel="stylesheet" type="text/css" href="./css/js.css" />
    <title></title>
    <meta name="author" content="Fred Cummins" />
    <meta name="description" content="Cognitive Science at UCD" />
    <meta name="keywords" content="Cognitive Science, Dublin, Ireland" />
    <script src="jquery-2.1.3.js"></script>
</head>
<body>
<div id="global">
<div id="header">

<img class="crest" height="109" alt="UCD Home" src="./images/ucd_crest_red.gif" width="89" />

<p id="logo">
<span id="whitetext">Joint Speech Archive</span></p> 

</div>  <!-- header -->

<div id="main_menu">

    <ul>
        <li><a href="index.php" title="Joint Speech Archive Home">[Home]</a>&nbsp;</li>
        <li><a href="add.php"  title="Add a video" >[Add Video]</a>&nbsp;</li>
        <li><a href="search.php"  title="Search" >[Search]</a>&nbsp;</li>
        <li><a href="http://cspeech.ucd.ie/JointSpeechExamples">[Examples]</a></li>

    </ul>
</div>

<div id="content" >

<?php
}

function endcontent()
{
?>
</div> <!-- end of content -->
<?php
}

function footer()
{
?>
<div id="footer">
	 
<p>Copyright by UCD School Of Computer Science and Informatics. 
Contact: <a href="mailto:fred.cummins@ucd.ie" 
title="Contact">fred.cummins@ucd.ie</a></p>


<p class="light">Disclaimer: the information contained in these web pages 
is, to the best of our knowledge, true and accurate at the time of publication, 
and is solely for informational purposes. University College Dublin accepts no 
liability for any loss or damage howsoever airising and a result of use or reliance 
on this information.</p>
</div>  <!-- footer -->
</div>  <!-- global -->

</body>
</html>
<?php
}

?>


