<?php
// respond to AJAX calls

include("/share1/proj/eem/templates/functions.php");




// Check Post Input

if (isset($_POST['id'])) {
	$id = $_POST['id'];
} else {
	die(encodeError("Id not specified."));
}

if (isset($_POST['name'])) {
	$name = $_POST['name'];
} else {
	die(encodeError("Name not specified."));
}



// Expression Example Upload Handler
if ('expressionExampleUpload'==$name) {
	if (isset($_POST['file'])) {
		$exampleFile = EXAMPLE_EX_PATH . trim($_POST["file"]);
		if (copyUploadedFile($exampleFile, EXPRESSION_ORIGINAL_FILE)) {
			die(encodeResult("Uploading Expression successful."));
		} else {
			die(encodeError("Uploading Expression Example failed, could not move file."));
		}
	}
	die(encodeError("Uploading Expression Example failed."));
}

// Expression Remote Upload Handler
if ('expressionRemoteUpload'==$name) {
	if (isset($_POST['file'])) {
		$remoteFile = trim($_POST["file"]);
		if (copyUploadedFile($remoteFile, EXPRESSION_ORIGINAL_FILE)) {
			die(encodeResult("Uploading Remote Expression successful."));
		} else {
			die(encodeError("Uploading Remote Expression failed, could not move file: ". $remoteFile));
		}
	}
	die(encodeError("Uploading Remote Expression failed."));
}



// Geneset Example Upload Handler
if ('genesetExampleUpload'==$name) {
	if (isset($_POST['file'])) {
		$exampleFile = EXAMPLE_GENE_PATH . trim($_POST["file"]);
		if (copyUploadedFile($exampleFile, GENESET_FILE)) {
			die(encodeResult("Uploading Geneset Example successful."));
		} else {
			die(encodeError("Uploading Geneset Example failed, could not move file: ". $exampleFile));
		}
	}
	die(encodeError("Uploading Geneset Example failed."));
}

// Geneset Remote Upload Handler
if ('genesetRemoteUpload'==$name) {
	if (isset($_POST['file'])) {
		$remoteFile = trim($_POST["file"]);
		if (copyUploadedFile($remoteFile, GENESET_FILE)) {
			die(encodeResult("Uploading Remote Geneset successful."));
		} else {
			die(encodeError("Uploading Remote Geneset failed, could not move file: ". $remoteFile));
		}
	}
	die(encodeError("Uploading Remote Geneset failed."));
}



// Preprocess Example Upload Handler
if ('preprocessExampleUpload'==$name) {
	if (isset($_POST['file'])) {
		$chipFile = EXAMPLE_PREPROCESS_PATH . trim($_POST["file"]);
		
		if (copyUploadedFile($chipFile, PREPROCESS_FILE)) {
			$additionalMessage = "Chip Example File successfully uploaded.";
			die(checkFiles($id, $additionalMessage));
			
		} else {
			die(encodeError("Uploading Chip Example File failed, could not move file: ". $chipFile));
		}
	}
	die(encodeError("Uploading Chip Example File failed: ". $chipFile ));
}



// Preprocess Remote Upload Handler
if ('preprocessRemoteUpload'==$name) {
	if (isset($_POST['file'])) {
		$chipFile = trim($_POST["file"]);
		
		if (copyUploadedFile($chipFile, PREPROCESS_FILE)) {
			checkFiles($id);
		} else {				
			die(encodeError("Uploading Remote Chip File failed, could not move file: ". $exampleFile));
		}
	}
	die(encodeError("Uploading Remote Chip File failed."));
}







function copyUploadedFile($inputFileName, $fileName) {
	global $id;
	$dir = DATAPATH . "$id/";
	
	// Create folder, if it does not exist
	if (!file_exists($dir))
		@mkdir($dir);
		
	
	$outputPath = $dir . $fileName;
	
	// open & save
	if ($data = file_get_contents($inputFileName)) {
		if (file_put_contents($outputPath, $data)) {
			return true;
		}
	}
	
	return false;
}



// Check Files
if ('fileCheck'==$name) {
	// check files
	die(checkFiles($id));		// return reults as JSON
}






// Preprocess Expression File

if ('preProcessExpression'==$name) {
	global $id;
	$dir = DATAPATH . "$id";
	
	//$type = $_POST['type']; // has to be expression
	
	
	if (!file_exists("$dir/expression_original.tab")) {
		die(encodeError("Preprocessing failed, Expression file does not exist."));
	}
		
	
	// optional chip file
	$chip = "";	
	if( in_array($_POST['type'], array("example", "remote", "local")) ) {
		$chipfile = "$dir/preprocess.chip";
		if (file_exists($chipfile)) {
			$chip = " -c $chipfile";
		}
	}
	
	// logscale
	$logscale = "";
	if($_POST['logscale']=="true") {
		$logscale = " -l";
	}

	// variation
	$variations = 8000;
	if ($_POST['variations'] && is_numeric($_POST['variations'])) {
		$variations = $_POST['variations'];
	}
	
	// reset preprocess bit
	$meta = get_meta();
	$meta["preprocess"] = false;
	update_meta($meta);
	
	
	chdir($dir);
	$command = "perl /share1/proj/eem/perl/preprocess2.pl$chip expression_original.tab$logscale -v $variations -i $id -o expression.tab";
	
	$ret = null;
	$output = array();
	exec($command, $output, $ret);
	
	// can't reply on $output and $ret to return success or error of preprocess2.pl... 
	// in preprocess_finished.php, success bit is write to id folder
		
	// check if $ok or $ng have been written by preprocess_finished.php
	$counter = 0;	
	while(true) {
		// check if preprocess is finished
		$meta = get_meta();
		if ($meta["preprocess"]) break;
		
		if ($counter>30) {							 // 30 seconds time-out
			die(encodeError("Preprocessing timed out."));
		}
		sleep(1);
		$counter++;
	}
	
	if ($meta["preprocess"]=="ok") {
		die(encodeResult("Preprocessing Successful."));
	}
	die(encodeError("Preprocessing failed."));

}




// Start Supercomputer
// done via 'normal' form

if ('startSupercomputer'==$name) {

	// job gets submitted in status.php (why?)
	// Ok
	die('{"jsonrpc" : "2.0", "result" : {"message": "Supercomputer started."}, "id" : "'.$id.'"}');
	
}


// Register Email Supercomputer

if ('registerEmail'==$name) {
	global $id;
	$email = $_POST['userEmail'];

	if (isset($_POST['userEmail'])) {
		$meta['email'] = trim($_POST['userEmail']);
		update_meta($meta);
		die(encodeResult("Email registered: ".$email));		
	}
	die(encodeError("Email registering failed: " . $email));	
}


if ('checkIfFinished'==$name) {
	global $id;
	$meta = get_meta();									// check meta, if its done..
	if ($meta['status']==JOB_FINISHED) {
		die(encodeResult("Done."));		
	}
	die(encodeError("Not yet finished."));	
}








die(encodeError("Could not complete Ajax request."));	


?>