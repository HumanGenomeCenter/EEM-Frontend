<pre>
<?php 

call('whoami');

echo "<b>time()</b>\n" . time() . "\n\n";


call("qdel -u hgcwww");







function call($command) {
	echo "<b>" . $command . "</b>\n";
	system($command);
	echo "\n\n";
}

?>
</pre>
