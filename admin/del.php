<!DOCTYPE html>
<html>
<head></head>
<body>
<pre>
<?php 

call('whoami');

echo "\n\n<b>time()</b>\n" . time() . "\n\n";


/*

// manual array of dir to delete
$dirs = array(
"xdG8K",
"xeF4D",
"xmx9t",
"xrvQP",
"xx3rp",
"xys1z"
 );


foreach ($dirs as $dir) {
	call("rm -r /share1/proj/eem/htdocs/upload/NONE/");
}

*/


function call($command) {
	echo "<b>" . $command . "</b>\n";
	$response = exec($command . " 2>&1");
	echo $response;
}

?>
</pre>
</body>
</html>
