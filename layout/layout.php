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
    <link rel="stylesheet" type="text/css" href="<?php echo $top ?>/css/whiteboards.css" />
    <title></title>
    <meta name="author" content="Fred Cummins" />
    <meta name="description" content="Whiteboards" />
    <meta name="keywords" content="Drivel and musings" />
</head>
<body>
<div id="global">
<div id="header">

<img class="crest" height="109" alt="Boo" src="<?php echo $top ?>/images/fred.gif" width="89" />

<p id="logo">
<span id="whitetext">Whiteboard project</span></p> 

</div>  <!-- header -->

<div id="main_menu">

    <ul>
        <li><a href="<?php echo $top; ?>/index.php" title="Whiteboard home">[Home]</a>&nbsp;</li>
        <li><a href="<?php echo $top; ?>/scripts/enterBoardUrl.php"  title="Add a board" >[Add Board]</a>&nbsp;</li>

    </ul>
</div>

<div id="content" >

<?php
}

function newHead($top, $runningHead)
{
   echo '<?xml version="1.0" encoding="iso-8859-15"?>' ;
?>
    
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en_GB">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15" />

    <link rel="stylesheet" type="text/css" href="<?php echo $top ?>/css/whiteboards.css" />
    <title></title>
    <meta name="author" content="Fred Cummins" />
    <meta name="description" content="Whiteboards" />
    <meta name="keywords" content="Drivel and musings" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

   <?php

}


function endcontent()
{
?>
</div> <!-- end of content -->
<div id="footer">
	 
<p>&copy; UCD School Of Computer Science. 
Contact: <a href="mailto:fred.cummins@ucd.ie" 
title="Contact">fred.cummins@ucd.ie</a></p>

<!--
<p class="light">Disclaimer: the information contained in these web pages 
is, to the best of our knowledge, true and accurate at the time of publication, 
and is solely for informational purposes. University College Dublin accepts no 
liability for any loss or damage howsoever airising and a result of use or reliance 
on this information.</p>
                    -->
</div>  <!-- footer -->
</div>  <!-- global -->

</body>
</html>
<?php
}

?>


