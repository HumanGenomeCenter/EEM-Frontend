<?php
include("/share1/proj/eem/templates/functions.php");

// from htaccess rewrite
// maps /x1234/image.png to /x1234/expression_geneset.eemHtml/image.png 


$id = $_GET['id'];
$document = $_GET['document'];
$path = DATAPATH . "$id/expression_geneset.eemHtml/$document";
$downloadPath = DATAPATH . "$id/$document";



if (file_exists($path)) {
	
	$info = pathinfo($path);
	$ext = $info['extension'];
	
	
	$png = (strtolower($ext)=="png");
	$jpg = ((strtolower($ext)=="jpg") || (strtolower($ext)=="jpeg"));
	$gif = (strtolower($ext)=="gif");
	$pdf = (strtolower($ext)=="pdf");
	$txt = (strtolower($ext)=="txt");
	
	if ($png || $jpg || $gif || $pdf) {
		
		if ($png) {
			$extension = "png";
		} elseif ($jpg) {
			$extension = "jpg";
		} elseif ($gif) {
			$extension = "gif";
		} elseif ($pdf) {
			$extension = "pdf";
		}
		
		
		// Getting headers sent by the client.
		$headers = apache_request_headers(); 

		// Checking if the client is validating his cache and if it is current.
		if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($path))) {
			// Client's cache IS current, so we just respond '304 Not Modified'.
 			header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($path)).' GMT', true, 304);
			
		} else {
			// Image not cached or cache outdated, we respond '200 OK' and output the image.
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($path)).' GMT', true, 200);
			header('Content-Length: '.filesize($path));
			header("Content-Type: image/$extension");
			print file_get_contents($path);
		}

	} else if($txt) {
		
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($path)).' GMT', true, 200);
			header('Content-Length: '.filesize($path));
			header("Content-Type: text/plain");
			print file_get_contents($path);
		
		
	} else {
		include($path);
	}
	
	

} else if(file_exists($downloadPath)) {

	/* error with Safari..
	"Frame load interrupted"
	But manually (double-clicking) starts the decompression process...
	
	*/
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$contentType = finfo_file($finfo, $downloadPath);
	
	header("Pragma: public");
	
	header("Cache-Control: private",false);
	
	//header('Content-type: application/octet-stream');
	header("Content-Type: $contentType");
	
	header('Content-Disposition: attachment; filename="' . $meta["zip"] . '"');
	header("Content-Transfer-Encoding: binary"); 
	header('Content-Length: ' . filesize($downloadPath));
	
	ob_clean(); 
	flush();
	readfile($downloadPath);
	//print file_get_contents($downloadPath);
	
} else {
	header("HTTP/1.0 404 Not Found");
	include("/share1/proj/eem/htdocs/404.html");
}





?>