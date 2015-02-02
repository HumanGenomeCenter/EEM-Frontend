<?php

include("/share1/proj/eem/templates/functions.php");
include("/share1/proj/eem/templates/uploadtext.php");		// forms & text for upload fields

// path to header & footer. default to '../', can be overrides from outside
if (!isset($path)) {
	$path = "../";
}


$has_expression_file = false;
$has_geneset_file = false;


// expression select
if ($_POST["ex_select"]=="local") {
	$ex_File = $_FILES["ex_local"];
	if ( isset($ex_File) && ($ex_File['error'] == UPLOAD_ERR_OK) ) {
		$has_expression_file = true;
	} else {
		$ex_error = "Could not upload local expression file";
	}
	
} elseif ($_POST["ex_select"]=="remote") {
	$ex_File = trim($_POST["ex_remote"]);
	if ($ex_File) { 				// filename > 0
		$ex_File = checkURL($ex_File);
		$file = fopen($ex_File, "r");
		if ($file) {
			$has_expression_file = true;
		} else {
			$ex_error = "Could not load remote expression file";
		}
		fclose($file);
	} else {
		$ex_error = "Please specify a remote expression file";
	}
	
} elseif ($_POST["ex_select"]=="example") {		// example		
	$ex_File = EXAMPLE_EX_PATH . trim($_POST["ex_example"]);			// relative path
	$file = fopen($ex_File, "r");
	if ($file) {
		$has_expression_file = true;
	} else {
		$ex_error = "Could not load example expression file";
	}
	fclose($file);
}



// geneset select
if ($_POST["gene_select"]=="local") {
	$gene_File = $_FILES["gene_local"];
	if ( isset($gene_File) && ($gene_File['error'] == UPLOAD_ERR_OK) ) {
		$has_geneset_file = true;
	} else {
		$gene_error = "Could not upload local geneset file";
	}
	
} elseif ($_POST["gene_select"]=="remote") {
	$gene_File = trim($_POST["gene_remote"]);
	if ($gene_File) { 				// filename > 0
		$gene_File = checkURL($gene_File);
		$file = fopen($gene_File, "r");
		if ($file) {
			$has_geneset_file = true;
		} else {
			$gene_error = "Could not load remote geneset file";
		}
		fclose($file);
	} else {
		$gene_error = "Please specify a remote geneset file";
	}
	
} elseif ($_POST["gene_select"]=="example") {		// example		
	$gene_File = EXAMPLE_GENE_PATH . trim($_POST["gene_example"]);			// relative path
	$file = fopen($gene_File, "r");
	if ($file) {
		$has_geneset_file = true;
	} else {
		$gene_error = "Could not load example geneset file";
	}
	fclose($file);
}




if (empty($_POST)) {  // initally
	$session_in_progress = false;			// start new session
	session_id( generateSessionID() );	// generate new id once
} else {
	$session_in_progress = true;
}

session_start();
$id = session_id();

/*
// diagnostics
print_r($_POST);
print_r($_FILES);

echo "session_in_progress: $session_in_progress\n";

print_r(get_meta());
*/

include($path . "header.php");



// show Introduction
showIntroductionMessage();


// File Size Restrictions
$maxContentLength = intVal(ini_get('upload_max_filesize')) * 1014 * 1024;		// in bytes
$contentLength = $_SERVER['CONTENT_LENGTH'];
$uploadedFileTooLarge = (isset($contentLength) && ($contentLength > $maxContentLength));



if ($session_in_progress) :	 

	// create directory
	$sessionDir = DATAPATH . $id . "/";
	if (!is_dir($sessionDir)) {
		mkdir($sessionDir, 0777, true);   			// create id dir, if it does not exist, defaults 0777
		
	}

	$ex_error = $gene_error = false;					// Initially no errors...
	

//	if ( ($_POST["file_check"]=="yes") &&  (($meta['geneset_status']==FILE_UPLOADED) && ($meta['expression_status']==FILE_UPLOADED)) ) {
	if ( ($_POST["file_check"]=="yes") ) {

		// check files
		
		$fileCheck = "perl /share1/proj/eem/perl/checkFormat.pl ".DATAPATH.$id."/expression.tab ".DATAPATH.$id."/geneset.gmt 2>&1";  // combine stderr with stdout
		$fileCheck_response = exec($fileCheck);
		
		if (!$fileCheck_response) {
			// if no response, file check succeeded
			update_meta(array('geneset_status'=>FILE_CHECKED));
			update_meta(array('expression_status'=>FILE_CHECKED));
		} else {
			$ex_error = $gene_error = "Your files seems to be inconsistent, please check them again.";
		}
		
	}
	

	if ( !($meta['geneset_status']>=FILE_UPLOADED) && !($meta['expression_status']>=FILE_UPLOADED)) {
		
		if ($has_expression_file) {
			// move into place
			if (moveUploadedFile($ex_File, 'expression.tab')) {  // or meta ex = true
				update_meta(array('expression_status'=>FILE_UPLOADED, 'session_id'=>$id));
			} else {
				// show upload code again
				$ex_error = "Could not copy the Expression file...";
			}
		} 

		if ($has_geneset_file) {
			// move into place
			if (moveUploadedFile($gene_File, 'geneset.gmt')) {  // or meta ex = true
				update_meta(array('geneset_status'=>FILE_UPLOADED, 'session_id'=>$id));
			} else {
				$gene_error = "Problem with Geneset file...";
			}
		}
		
		// Check if the files have been successfully uploaded. If so, check the Files
		$meta = get_meta();
		$filesUploaded = ($meta['geneset_status']==FILE_UPLOADED) && ($meta['expression_status']==FILE_UPLOADED);
		if ($filesUploaded) {
			checkFiles();
		}
		
	}

	
	showUploadForm($ex_error, $gene_error); 
	

elseif($uploadedFileTooLarge):
	$contentLengthInMB = sprintf("%01.2f", $contentLength / 1024 / 1024) ;
	$maxContentLengthInMB = sprintf("%01.2f", $maxContentLength / 1024 / 1024);
	echo "<h3>Upload Error: File Size too Large</h3>";
	echo "\n<br>Your uploaded file has a size of $contentLengthInMB MB. Unfortunately our server can only accomodate files up to $maxContentLengthInMB MB in size. ";
	echo "Please upload your file to a public server and use the 'Remote File' option. Or reduce the size of your file. ";
	echo "\n<br><br>Return to the <a href='https://eem.hgc.jp'>EEM Upload Page</a>.";

else: // session not in progress, start New Session

	showInitialUploadForm();
endif; 




function checkFiles() {
	// check files
	global $id, $ex_error, $gene_error;
	
	$fileCheck = "perl /share1/proj/eem/perl/checkFormat.pl ".DATAPATH.$id."/expression.tab ".DATAPATH.$id."/geneset.gmt 2>&1";  // combine stderr with stdout
	$fileCheck_response = exec($fileCheck);
	
	if (!$fileCheck_response) {
		// if no response, file check succeeded
		update_meta(array('geneset_status'=>FILE_CHECKED));
		update_meta(array('expression_status'=>FILE_CHECKED));
	} else {
		$ex_error = $gene_error = "Your files seems to be inconsistent, please check them again.";
	}
}





include($path . "footer.php"); ?>