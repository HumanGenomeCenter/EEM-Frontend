<pre>
<?php 

error_reporting(E_ALL);

call('whoami');

echo "<b>time()</b>\n" . time() . "\n\n";

	$qsub = "qsub -q web.q -cwd /share1/proj/eem/perl/eem.pl /share1/proj/eem/data/xp2gq/expression.tab /share1/proj/eem/data/xp2gq/geneset.gmt -X xp2gq";
	call($qsub);
	
	//Your job 3248670 ("eem.pl") has been submitted






function call($command) {
	echo "<b>" . $command . "</b>\n";
	system($command);
	echo "\n\n";
}

?>
</pre>
