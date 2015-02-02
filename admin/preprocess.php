<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>EEM: Extraction of Expression Module</title>
</head>
<body>
	
<pre>
	
<form action="preprocess.php" method="post" accept-charset="utf-8">

<input type="hidden" name="submitted" value="yes" id="some_name">

<label for="">Chip file: </label><input type="text" name="chip" id="" size="80" value="human.chip" >


<label for="">Convert Expression Value to the log-scale.</label><input type="checkbox" name="logscale" value="yes" id="logscale">


<label for="">Number of genes after variation filter <em>(default=8000)</em></label><input type="number" name="variations" id="variations" value="8000" min="1000" max="20000">



<?php 

include("/share1/proj/eem/templates/functions.php");


$submittime = time();

$id = "xNWGn";
$dir = DATAPATH . $id;
chdir($dir);


$chip = trim($_POST['chip']);

if (strlen($chip)>0) {
	$chip = " -c /share1/proj/eem/data/chip/" . trim($_POST['chip']);
}

$logscale = isset($_POST['logscale']) ? " -l" : "";

$variations = $_POST['variations'];

echo "chippath: $chip\n";
echo "logscale: $logscale\n";
echo "variations: $variations\n\n";

$command = "perl /share1/proj/eem/perl/preprocess2.pl$chip expression.tab$logscale -v $variations -i $id -o out.tab";
echo "command: $command\n";



/*
//rmdir("test");
//echo getcwd() . "\n";

// $preprocess = "perl /share1/proj/eem/perl/preprocess2.pl -c /share1/proj/eem/data/chip/human.chip expression.tab -l -v 1000 -i $id -o out.tab";

$preprocess = "perl /share1/proj/eem/perl/preprocess2.pl expression.tab -l -v 1000 -i $id -o out.tab";
//$preprocess = "perl /share1/proj/eem/perl/preprocess2.pl";
//$preprocess = "perl /share1/proj/eem/perl/preprocess2.pl expression.tab -l -v 1000 -i $id 2>&1";
*/
$ret = null;
exec($command, $output, $ret);
//print_r($output);



?>
</pre>

	<p><input type="submit" value="Submit &rarr;"></p>
</form>

<?php if (isset($_POST['submitted'])) {
	$response = $ret ? "OK": "NG";
	?>
ID: <?php echo $id; ?><br>
Submit Time: <?php echo $submittime; ?>ms<br>
Respond Time: <?php echo $respondtime = time(); ?>ms<br>
Duration = <?php echo $respondtime - $submittime; ?>ms<br>
<h1>Response: <?php echo $response;

}
?>
</h1>

Raw Output
<?php

print_r($output);
print_r($ret);

?>

</body>
