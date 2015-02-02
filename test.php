<pre>
<?php
// respond to AJAX calls

include("/share1/proj/eem/templates/functions.php");





$copy = copyUploadedFile("http://www.hgc.jp/~tremmel/test.tab", 'expression_remote.tab');

if ($copy) {
	echo "ok";
} else {
	"error";
}




function copyUploadedFile($inputFileName, $fileName) {
	global $id;
	$id = "xfVC6";
	$dir = DATAPATH . "$id/";
	
	echo $dir;
	
	// Create folder, if it does not exist
	if (!file_exists($dir))
		@mkdir($dir);
		
	
	$outputPath = $dir . $fileName;
	
	/*
	if ($_POST[$selector]=="local") {
		if (move_uploaded_file($inputFileName['tmp_name'], $outputPath)) {
			return true;
		 } 
	} 
	
	if ($_POST[$selector]=="remote") {
		// open & save
		$data = file_get_contents($inputFileName);
		file_put_contents($outputPath, $data);
		return true;
	}
	*/
		
	// copy? permissions... open & save 
	// open & save
	if ($data = file_get_contents($inputFileName)) {
		if (file_put_contents($outputPath, $data)) {
			return true;
		}
	}
	
	return false;
}




?>
</pre>