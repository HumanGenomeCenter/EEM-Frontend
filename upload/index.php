<?php

// path to header & footer. default to '../', can be overrides from outside
if (!isset($path)) {
	$path = "../";
}

include("/share1/proj/eem/templates/functions.php");
include($path . "includes/uploadtext.php");		// forms & text for upload fields



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

$meta = get_meta();
include($path . "header.php");



// show Introduction
showIntroductionMessage();

// File Size Restrictions
$maxContentLength = intVal(ini_get('upload_max_filesize')) * 1024 * 1024;		// in bytes
$contentLength = $_SERVER['CONTENT_LENGTH'];
$uploadedFileTooLarge = (isset($contentLength) && ($contentLength > $maxContentLength));





if ($session_in_progress) :	 

	// create directory
	$sessionDir = DATAPATH . $id . "/";
	if (!is_dir($sessionDir)) {
		mkdir($sessionDir, 0777, true);   			// create id dir, if it does not exist, defaults 0777
	}

	
	
	if ( ($_POST["file_check"]=="yes") ) {

		// check files
		
		$fileCheck = "perl /share1/proj/eem/perl/checkFormat.pl ".DATAPATH.$id."/expression.tab ".DATAPATH.$id."/geneset.gmt 2>&1";  // combine stderr with stdout
		$fileCheck_response = exec($fileCheck);
		
		if (!$fileCheck_response) {
			// if no response, file check succeeded
			update_meta(array('geneset_status'=>FILE_CHECKED));
			update_meta(array('expression_status'=>FILE_CHECKED));
			$ex_error = $gene_error = false;	
			
		} else {
			$ex_error = $gene_error = "Your files seems to be inconsistent, please check them again.";
		}
		
	}
	

	// only for remote & examples. local is specified in upload.php
	if ( !($meta['geneset_status']>=FILE_UPLOADED) && !($meta['expression_status']>=FILE_UPLOADED)) {
		
		if ($has_expression_file) {
			// move into place
			if (moveUploadedFile($ex_File, 'expression.tab')) {  // or meta ex = true
				update_meta(array('expression_status'=>FILE_UPLOADED, 'session_id'=>$id));
				$ex_error = false;	
			} else {
				// show upload code again
				$ex_error = "Could not copy the Expression file...";
			}
		} 

		if ($has_geneset_file) {
			// move into place
			if (moveUploadedFile($gene_File, 'geneset.gmt')) {  // or meta ex = true
				
				// store preprocessing arguments

				if(isset($_POST["logscale"]) && $_POST["logscale"]=='yes') {
					$logscale = 'yes';
				} else {
					$logscale = 'no';
				}

				if(isset($_POST["variations"]) ) {
				
					if (is_numeric($_POST["variations"])) {
						$variations = $_POST["variations"];
					} else {
						$variations = 8000;
					}
				}

				
				
				update_meta(array('geneset_status'=>FILE_UPLOADED, 'session_id'=>$id, 'variations'=>$variations, "logscale"=>$logscale));
				$gene_error = false;
			} else {
				$gene_error = "Problem with Geneset file...";
			}
		}
		
	}

	
	showUploadForm($ex_error, $gene_error); 
	



else: // session not in progress, start New Session
	showInitialUploadForm();
endif; 



?>











<script type="text/javascript" src="https://eem.hgc.jp/js/plupload.js"></script>
<script type="text/javascript" src="https://eem.hgc.jp/js/plupload.flash.js"></script>
<script type="text/javascript" src="https://eem.hgc.jp/js/plupload.html4.js"></script>
<script type="text/javascript" src="https://eem.hgc.jp/js/plupload.html5.js"></script>

<script type="text/javascript">
// Custom example logic

var finished = {};
finished.expresssion = false;
finished.geneset = false;
finished.check = function() {
	console.log(finished.expresssion, finished.geneset);
	
	if (finished.expresssion && finished.geneset) {
		// change upload button
		
		$('form #upload').val('Check File Consistency');
		

		
		// append hidden form
		$('form').append( $('<input>').attr({type: 'hidden', name: 'file_check', value:"yes"}) );
		
		
		
		
	}
};



// Expression
var uploader;
$(document).ready(function() {
	uploader = new plupload.Uploader({
		runtimes : 'html5,flash,html4',
		browse_button : 'pickfiles',
		container: 'container',
		max_file_size : '100mb',
		chunk_size: '1mb',			// chunking to prevent php upload limit
		url: "/upload/upload.php?type=expression&id=<?php echo $id; ?>",
//		urlstream_upload: true,
		multiple_queues: true,
		multipart: true,
		max_file_count : 1,
		flash_swf_url : '../js/plupload.flash.swf',
		filters : [
			{title : "Image files", extensions : "jpg,gif,png"},
			{title : "Zip files", extensions : "zip,*"}
		],
		init : {
			FilesAdded: function(up, files) {
				up.start();		// start after adding
			},
			UploadComplete: function(up, files) {
				finished.expresssion = true;
				finished.check();
			}
		}
	});

	uploader.bind('Init', function(up, params) {
		console.log("Current runtime: " + params.runtime);
	});

	uploader.bind('FilesAdded', function(up, files) {
	//	console.log(files);
		for (var i in files) {
			$('#filelist').text(files[i].name + ' (' + plupload.formatSize(files[i].size) + ')');
		}
	});

	uploader.bind('UploadProgress', function(up, file) {
	//	console.log(file.percent);
		$('#uploadProgress').text(file.percent + "%");
	});
	
	uploader.init();
});

var uploader2;
$(document).ready(function() {
	
	uploader2 = new plupload.Uploader({
		runtimes : 'html5,flash,html4',
		browse_button : 'pickfiles2',
		container: 'container2',
		max_file_size : '100mb',
		chunk_size: '1mb',
		url: "/upload/upload.php?type=geneset&id=<?php echo $id; ?>",
		urlstream_upload: true,
		multiple_queues: true,
		max_file_count : 1,
		flash_swf_url : '../js/plupload.flash.swf',
		filters : [
			{title : "Image files", extensions : "jpg,gif,png"},
			{title : "Zip files", extensions : "zip,*"}
		],
		init : {
			FilesAdded: function(up, files) {
				up.start();		// start after adding
			},
			UploadComplete: function(up, files) {
				finished.geneset = true;
				finished.check();
			}
		}
	});

	uploader2.bind('Init', function(up, params) {
		// console.log("Current runtime: " + params.runtime);
	});

	uploader2.bind('FilesAdded', function(up, files) {
	//	console.log(files);
		for (var i in files) {
			$('#filelist2').text(files[i].name + ' (' + plupload.formatSize(files[i].size) + ')');
		}
	});

	uploader2.bind('UploadProgress', function(up, file) {
	//	console.log(file.percent);
		$('#uploadProgress2').text(file.percent + "%");
	});
	
	uploader2.init();
});

</script>



<?php 

include($path . "footer.php"); 

?>
