<?php

// Include Functions
$path = "../";
include("/share1/proj/eem/templates/functions.php");
include($path . "includes/uploadtext.php");		// forms & text for upload fields


// Start Session
$session_in_progress = true;
if (empty($_POST)) {  // initally
	$session_in_progress = false;		
	session_id( generateSessionID() );	// generate new id once
} 

session_start();								// start new session
$id = session_id();							// get session id	
	

// header
include($path . "header.php");


// show Introduction
showIntroductionMessage();




?>

<div id="selectFiles">
<form method="post" action="./" enctype="multipart/form-data">
<input type="hidden" name="id" value="<?php echo $id; ?>">
<table id="uploadtable">

	<tr>
		<td class="bold">Session ID</td>
		<td id="sessionID"><?php echo $id; ?></td>
	</tr>

	<tr>
		<td class="bold">Expression Dataset</td>
		<td>
			<div id="expression">

				<select id="select_expression" name="ex_select">
					<option value="local" selected>Local File</option>
					<option value="remote">Remote File</option>
					<option value="example">Example</option>
				</select>
				
				<div class="local">
					<span id="expressionContainer">
						<input id="expressionChooser" type="submit" value="Choose File" size="40">
						<span id="expressionFileList"></span>
						<span id="expressionUploadProgress" class="progress"></span>
					</span>
				</div>
				
				<label for="ex_remote" class="remote">http://<input type="url" name="ex_remote" placeholder="url of your expression dataset" size="40" ></label>
				
				<select class="example" name="ex_example">
					<option value="test.tab">Breast tumor microarray data</option>
				</select>
				
				<div class="more"></div>
				<div class="small">
					This file includes features (genes or probes), samples, and an expression 
					value for each feature in each sample. The two file formats are available: 
					A simple tab-delimited format for expression dataset (*.tab,  <a href="https://eem.hgc.jp/examples/expression/test.tab">example</a>),
					or the GCT  Text file format (*.gct),  which are supported in 
					<a href="http://www.broadinstitute.org/gsea/">GSEA</a>.                          
				</div>
			</div>
		</td>
	</tr>

	<tr>
		<td class="bold">Gene Sets</td>
		<td>
			<div id="geneset">
				<select id="select_geneset" name="gene_select">
					<option value="local" selected>Local File</option>
					<option value="remote">Remote File</option>
					<option value="example">Example</option>
				</select>
				
				<div class="local">
					<span id="genesetContainer">
						<input id="genesetChooser" type="submit" value="Choose File" size="40">
						<span id="genesetFileList"></span>
						<span id="genesetUploadProgress" class="progress"></span>
					</span>
				</div>
				
				<label for="gene_remote" class="remote">http://<input type="url" name="gene_remote" placeholder="url of your geneset" size="40" ></label>
				
				<select class="example" name="gene_example">
					<option value="test.gmt"> gene sets associated with common GO terms </option>
				</select>
			
				<div class="small">
					<a href="#" id="showAdvancedOptions">Advanced Options...</a>
					<div id="advancedOptions">
						<table id="advancedOptionsTable">
							<tr>
								<td><input type="checkbox" name="logscale" value="yes" id="logscale"></td>
								<td>Convert Expression Value to the log-scale.</td>
							</tr>
							<tr>
								<td><input type="number" name="variations" id="variations" value="8000" min="1000" max="20000"></td>
								<td>Number of genes after variation filter <em>(default=8000)</em></td>
							</tr>
						</table>
					</div>
				</div>
				
				
				<div class="small">
					This file includes one or more predefined gene sets like gene sets from the Molecular 
					Signature Database (<a href="http://www.broadinstitute.org/gsea/msigdb/">MSigDB</a>) or your own defined gene sets. For each gene set, 
					the gene set name, its brief description, and genes in the gene set are given. 
					The file format should be followed by the GMT file format 
					(*.gmt, <a href="https://eem.hgc.jp/examples/geneset/test.gmt">example</a>).
				</div>
				
			
			</div>
		</td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td class="right">
			<div id="checkResponse" class="left small attention">Please check the format of your files...</div>
			<input id="start" type="submit" value="Start Calculation" disabled="disabled">
		</td>
	</tr>
</table>
</form>
</div>








<script type="text/javascript">
// Custom example logic



function uploadFinished(type, uploader) {
	//console.log("upload " + type + " finished.", uploader.files);
	
}

var test;

var ajaxURL = "https://eem.hgc.jp/ajax.php";
var id = $('#sessionID').html();
function createUploader(type) {
	// upload factory
	
	uploader = new plupload.Uploader({
		runtimes : 'html5,flash,html4',
		browse_button : type+'Chooser',
		container: 'container',
		max_file_size : '100mb',
		chunk_size: '1mb',																// chunking to prevent php upload limit
		url: "/upload/upload.php?type="+type+"&id="+id,						// pass msg along 
		urlstream_upload: true,
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
				up.start();		// start uploading file right after adding
				$("#checkResponse").fadeOut(500);		// hide error msg
			},
			UploadComplete: function(up, files) {
				//console.log(up, uploader);
				uploadFinished(type, up);
			},
			Error: function(up, error) {
				console.log("Error", up, error);
			},
			FileUploaded: function(up, file, res) {
				var result = JSON.parse(res.response).result;
				if (result.expression && result.geneset) {
					// AJAX check files
					console.log("ajax, send checking files request");
					
					$.post(ajaxURL, {'name':'fileCheck','id':id}, function(r) {
						var response = JSON.parse(r);
						if (response.result) {
							// ok
							$("#start").attr('disabled', false);	// enable start button
						} else {
							// error
							$("#checkResponse").fadeIn(500);
						}						
					});
					
				}
			}
		}
	});

	// check for upload completness with uploader.files.length
	
	/*
	uploader.bind('Init', function(up, params) {
		console.log("Current runtime: " + params.runtime);
	});
	*/
	
	uploader.bind('FilesAdded', function(up, files) {
	//	console.log(files);
		for (var i in files) {
			$('#'+type+'FileList').text(files[i].name + ' (' + plupload.formatSize(files[i].size) + ')');
		}
	});

	uploader.bind('UploadProgress', function(up, file) {
		$('#'+type+'UploadProgress').text(file.percent + "%");			// update upload progress
	});
	
	uploader.init();
	
	return uploader;
	
}






// Uploaders
var expression;
var geneset;
var genesetOptions;

// Create Uploaders
$(document).ready(function() {
	expression = createUploader('expression');
	geneset = createUploader('geneset');
	genesetOptions = createUploader('genesetOptions');
});



</script>


<?php include($path . "footer.php"); ?>