<pre>
<?php 

$DATAPATH = "/share3/home/w3eem/proj_home/dataTmp";
error_reporting(E_ALL);

$cwd = getcwd();
echo $cwd . "\n\n";

$id = "xWR24";
$zipFileName = "EEM_$id.zip";
call("tar -czvpf $zipFileName ". $DATAPATH .$id . "/expression_geneset.eemHtml");

/*
	// Create Zip file for downloading
	chdir(DATAPATH . $id);
	
	system("tar -czpf $zipFileName expression_geneset.eemHtml"); 
	$meta['zip'] = $zipFileName;
	update_meta($meta);

*/




print_r( scandir($cwd) );


function call($command) {
	echo "<b>" . $command . "</b>\n";
	system($command . " 2>&1");
	echo "\n\n";
}

?>
</pre>
