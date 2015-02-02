<pre>
<?php 
include("/share1/proj/eem/templates/functions.php");

error_reporting(E_ALL);


// testing automatic data deletion
deleteData();



function call($command) {
	echo "<b>" . $command . "</b>\n";
	system($command . " 2>&1");
	echo "\n\n";
}

?>
</pre>
