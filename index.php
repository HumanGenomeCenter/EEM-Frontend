<?php

// Include Functions
$path = "./";
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
<form method="post" action="./<?php echo $id; ?>" enctype="multipart/form-data">
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="start" value="yes">
<table id="uploadtable">

	<tr>
		<td class="bold">Session ID</td>
		<td id="sessionID"><?php echo $id; ?></td>
	</tr>

	<tr>
		<td class="bold">
			Expression Dataset<br />
			<span id="expressionStatus" class="progress"></span>
		</td>
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
				
				<span class="remote">
					<label for="ex_remote" ><input id="ex_remote" type="url" name="ex_remote" placeholder="http://..." size="30" ></label>
					<button id="use">Upload</button>
					<span id="expressionRemoteUploadProgress" class="progress"></span>
				</span>
				
				<span class="example">
					<select id="ex_example" name="ex_example">
						<option value="test.tab">Breast tumor microarray data</option>
					</select>
					<button id="use">Use</button>
					<span id="expressionExampleUploadProgress" class="progress"></span>
				</span>
			
				
				
			</div>
				<div id="preprocess" class="small">
					<a href="#" id="showAdvancedOptions">Advanced Preprocessing Options...</a>
					<div id="advancedOptions">
						<table id="advancedOptionsTable">
							<tr>
								<td colspan="2" class="em gray">Preprocess the expression file. Convert probe IDs to gene symbols.</td>
							</tr>
							<tr>
								<td colspan="2" class="gray">
									The CHIP file format contains annotation information about a microarray. 
									It lists the features (i.e probe sets) used in the microarray along with their mapping 
									to gene symbols (when available). <br />
									More information about the CHIP file format can be found <a href="http://www.broadinstitute.org/cancer/software/genepattern/gp_guides/file-formats/sections/chip">here</a>.
									</td>
							</tr>
							<tr>
								<td>
									<select id="select_preprocess" name="preprocess_select">
										<option value="none" selected>-</option>
										<option value="local">Local File</option>
										<option value="remote">Remote File</option>
										<option value="example">Example</option>
									</select>
								</td>
								<td>
									<div class="local">
										<span id="preprocessContainer">
											<input id="preprocessChooser" type="submit" value="Choose File" size="40">
											<span id="preprocessFileList"></span>
											<span id="preprocessUploadProgress" class="progress"></span>
										</span>
									</div>

									<span class="remote">
										<label for="preprocess_remote"><input id="preprocess_remote" type="url" name="preprocess_remote" placeholder="http://..." size="30" ></label>
										<button id="use">Upload</button>
										<span id="preprocessRemoteUploadProgress" class="progress"></span>
									</span>

									<span class="example">
										<select id="preprocess_example" name="preprocess_example">
											<option value="human.chip">human.chip</option>
										</select>
										<button id="use">Use</button>
										<span id="preprocessExampleUploadProgress" class="progress"></span>
									</span>
								</td>
							</tr>
							<tr>
								<td><input type="checkbox" name="logscale" id="logscale"></td>
								<td>Convert Expression Value to the log-scale.</td>
							</tr>
							<tr>
								<td><input type="number" name="variations" id="variations" value="8000" min="1000" max="20000"></td>
								<td>Number of genes after variation filter <em>(default=8000)</em></td>
							</tr>
							<tr>
								<td colspan="2"><button id="preprocessButton" disabled>Preprocess Expression...</button><span id="preprocessProgress" class="progress"></span></td>
							</tr>
						</table>
					</div>
				</div>
				
				<div class="more"></div>
				<div class="small">
					This file includes features (genes or probes), samples, and an expression 
					value for each feature in each sample. The two file formats are available: 
					A simple tab-delimited format for expression dataset (*.tab,  <a href="https://eem.hgc.jp/examples/expression/test.tab">example</a>),
					or the GCT  Text file format (*.gct),  which are supported in 
					<a href="http://www.broadinstitute.org/gsea/">GSEA</a>.                          
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
				
				<span class="remote">
					<label for="gene_remote">http://<input id="gene_remote" type="url" name="gene_remote" placeholder="http://..." size="30" ></label>
					<button id="use">Upload</button>
					<span id="genesetRemoteUploadProgress" class="progress"></span>
				</span>
				
				<span class="example">
					<select id="gene_example" name="gene_example">
						<option value="test.gmt"> gene sets associated with common GO terms </option>
					</select>
					<button id="use">Use</button>
					<span id="genesetExampleUploadProgress" class="progress"></span>
				</span>
			</div>
			
			
		
			<div class="small">
				This file includes one or more predefined gene sets like gene sets from the Molecular 
				Signature Database (<a href="http://www.broadinstitute.org/gsea/msigdb/">MSigDB</a>) or your own defined gene sets. For each gene set, 
				the gene set name, its brief description, and genes in the gene set are given. 
				The file format should be followed by the GMT file format 
				(*.gmt, <a href="https://eem.hgc.jp/examples/geneset/test.gmt">example</a>).
			</div>
				
			
			
		</td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td class="right">
			<span id="checkingMessage" class="left small message"></span>
			<input id="start" type="submit" value="Start EEM" disabled="disabled">
		</td>
	</tr>
</table>
</form>
</div>








<script type="text/javascript">
// Custom example logic



// Upload Factory
function createUploader(type) {
	uploader = new plupload.Uploader({
		runtimes : 'html5,flash,html4',
		browse_button : type+'Chooser',
		container: 'container',
		max_file_size : '100mb',
		chunk_size: '1mb',																// chunking to prevent php upload limit
		url: "/upload/upload.php?type="+type+"&id="+id,							// pass msg along 
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
				$("#expressionPreprocessProgress").html("");		// remove previous preprocessing message
			},
			UploadComplete: function(up, files) {
		//		console.log("Local Upload Complete");
			},
			Error: function(up, error) {
				console.log("Error", up, error);
			},
			FileUploaded: function(up, file, res) {
				console.log("Local "+ type + "File Uploaded", res);
				
				if (type==="expression") {
					console.log("Submit preprocess");
					submitPreprocessRequest();		// submitFileCheck is done after preprocessing
				}
				
				if (type==="geneset") {
					console.log("Submit preprocess");
					submitFileCheck();
				}
				
				if (type==="preprocess") {
				}
				
				
			}
		}
	});
	
	uploader.bind('FilesAdded', function(up, files) {
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
var preprocess;

// Create Uploaders
$(document).ready(function() {
	expression = createUploader('expression');
	geneset = createUploader('geneset');
	preprocess = createUploader('preprocess');
});



</script>


<?php include($path . "footer.php"); ?>