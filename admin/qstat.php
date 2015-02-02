<pre>
<?php 

call('whoami');

echo "<b>time()</b>\n" . time() . "\n\n";

call ("qstat -u hgcwww");

call("qstat -g c");

if ($_GET["job"]) {
	call("qstat -j " . $_GET["job"] );

}




function call($command) {
	echo "<b>" . $command . "</b>\n";
	system($command);
	echo "\n\n";
}

?>
</pre>
