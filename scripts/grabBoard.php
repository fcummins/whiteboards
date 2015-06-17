<?php
include_once("../php/FredsFreetag2.php");
include_once("../php/FredsWhiteboardFunctions.php");

$safeHtmlString = addslashes($_POST['htmlstring']);
                            ?>

<html><body><h1>Hi Fred!!!</h1>
<pre>
<?php  echo $safeHtmlString;?>
</pre>
</body></html>

