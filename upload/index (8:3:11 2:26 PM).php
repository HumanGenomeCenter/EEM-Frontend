<?php

include("/share1/proj/eem/templates/functions.php");



print_r($_FILES);

print_r($_POST);

// local file

$expression = $_FILES["expression"];
$geneset = $_FILES["geneset"];

/*
if ( (isset($expression) && ($expression['error'] == UPLOAD_ERR_OK)) && (isset($geneset) && ($geneset['error'] == UPLOAD_ERR_OK)) ) {
	$session_in_progress = true;
	session_start();
} else {
	// reset
	$session_in_progress = false;
	session_id( generateSessionID() );	// generate id once
	session_start();
}

$id = session_id();

*/
// $upload = true;

include("../header.php");

if ($session_in_progress) :	
	function moveUploadedFile($file, $name) {
		global $id;
		$sessionIncompletePath = '/share1/proj/eem/incomplete/' . $id . "/";
		system("mkdir " . $sessionIncompletePath);
		system("chmod 777 " . $sessionIncompletePath);
		
		if ($file == $_FILES["expression"]) {
			$outputName = "expression.tab";
		} elseif ($file == $_FILES["geneset"]) {
			$outputName = "geneset.gmt";
		} else {
			$outputName = "error.txt";		// just in case
		}
		
		$incompletePath = $sessionIncompletePath . $outputName;
		
		if (move_uploaded_file($file['tmp_name'], $incompletePath)) {
			//print "File saved in $incompletePath <br />\n<br />\n"; 
			return true;
	
		 } else {
			print "Couldn't move file to $incompletePath <br />\n<br />\n";
			return false;
		}
	}
	
	if (moveUploadedFile($expression, 'ex') && moveUploadedFile($geneset, 'gene')) {
		// move to data folder
		system("mv " . "/share1/proj/eem/incomplete/" . $id . "/ " . "/share1/proj/eem/data/");
	
		// create status file
		// create status file
		$meta = array(	'status'=>FILES_CHECKED, 
						'session_id'=>$id
				);
		update_meta($meta);
?>

<form method="post" action="<?php echo "../" . $id; ?>/">
<table id="uploadtable">
	<tr>
		<td class="bold">Status</td>
		<td>Expression and Geneset Files successfully uploaded.</td>
	</tr>
	<tr>
		<td class="bold">Session ID</td>
		<td><?php echo $id; ?></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td class="right">
			<input type="hidden" name="start" value="yes">
			<input id="upload" type="submit" value="Start Super-Computer">
		</td>
	</tr>
</table>	
</form>




<?php
	
	} else {
		
	}
		
?>

<?php else: ?>

<div id="selectFiles">
	
<form method="post" action="./" enctype="multipart/form-data">
<table id="uploadtable">
	<!--
	<tr>
		<td class="bold">Session ID</td>
		<td><?php echo $id; ?></td>
	</tr>
-->
	<tr>
		<td class="bold">Expression Dataset</td>
		<td>
			<div id="expression">
			<select id="select_expression" name="ex_select">
				<option value="local" selected>Local File</option>
				<option value="remote">Remote File</option>
				<option value="example">Examples</option>
			</select>
			<input type="file" name="ex_local" size="40" id="local">
			<label for="ex_remote"id="remote">http://<input type="url" name="ex_remote" placeholder="www.cerchjeirujcuie.com" size="40" ></label>
			<select id="example" name="ex_example">
				<option value="breastMiller.tsv">Breast tumor microarray data</option>
			</select>
			<div class="more"></div>
			<div class="small">
				This file includes features (genes or probes), samples, and an expression 
				value for each feature in each sample. The two file formats are available: 
				GCT, Gene Cluster Text file format (*.gct), and TXT, Text file format for 
				expression dataset (*.txt), which are supported in <a href="http://www.broadinstitute.org/gsea/">GSEA</a>.
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
				<option value="example">Examples</option>
			</select>
			<input type="file" name="gene_local" size="40" id="local">
			<label for="gene_remote"id="remote">http://<input type="url" name="gene_remote" placeholder="www.cerchjeirujcuie.com" size="40" ></label>
			<select id="example" name="gene_example">
				<option value="PWM.gmt">TF target gene set library </option>
			</select>
			<div class="small">
				This file includes one or more predefined gene sets like gene sets from the Molecular 
				Signature Database (<a href="http://www.broadinstitute.org/gsea/msigdb/">MSigDB</a>) or your own defined gene sets. For each gene set, 
				the gene set name, its brief description, and genes in the gene set are given. 
				The file format should be followed by GMT, Gene Matrix Transposed file format (*.gmt).
			</div>
			</div>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td class="right"><input id="upload" type="submit" value="Upload your Files"></td>
	</tr>
</table>
</form>

</div>
<?php endif; ?>



<?php include("../footer.php"); ?>