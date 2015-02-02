<?php

include("/share1/proj/eem/templates/functions.php");


$id = "xVctv";
chdir(DATAPATH . $id);	 // necessary

$fileCheck = "perl /share1/proj/eem/perl/checkFormat.pl ".DATAPATH.$id."/expression.tab ".DATAPATH.$id."/geneset.gmt 2>&1";
$fileCheck_response = exec($fileCheck);

if (!$fileCheck_response) {
	echo "files ok.";
} else {
	echo $fileCheck_response;
}
?>