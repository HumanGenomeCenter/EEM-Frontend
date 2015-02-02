<?php

function showIntroductionMessage() {
?>
<p>EEM needs two types of inputs: an expression dataset of interest and a gene set library that includes gene sets used as seeds of modules. EEM assumes coherence as an indication of the functionality of gene sets; i.e., if a gene set have some function, the member of the gene set should be coherently expressed in the expression dataset. Under this assumption, EEM screens a gene set library for gene sets that have a signifcantly large coherent subset in the expression data set. From the gene sets that passed the screening, EEM extracts coherent subsets as expression modules.
</p>

<div class="imagewrapper"><img src="/images/reem-explanation.png" height="280" width="450" /></div>

<?php
}


function showUploadForm($ex_error, $gene_error, $initial = false) {
	
	$id = session_id();
	$meta = get_meta();	// // get meta array
	


	if (!$initial) {
	
		if (!$ex_error && !$gene_error && ($meta['geneset_status']==FILE_UPLOADED) && ($meta['expression_status']==FILE_UPLOADED) ) {
			// No Errors, proceed to file check
			?>
<div id="selectFiles">
<form method="post" action="./">
<table id="uploadtable">
	<tr>
		<td class="bold">Session ID</td>
		<td id="sessionID"><?php echo $id; ?></td>
	</tr>
	<tr>
		<td class="bold">Status</td>
		<td>Expression and Geneset Files successfully uploaded. Please run the File Checker.</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td class="right">
			<input type="hidden" name="file_check" value="yes">
			<input id="upload" type="submit" value="Check File Consistency">
		</td>
	</tr>
</table>	
</form>
</div>		
<?php



			return; // to prevent further ouput
		}
		
		
		
		if (!$ex_error && !$gene_error && ($meta['geneset_status']==FILE_CHECKED) && ($meta['expression_status']==FILE_CHECKED) ) {
			// Files checked and OK
			?>
<div id="selectFiles">
<form method="post" action="<?php echo "../" . $id; ?>/">
<table id="uploadtable">
	<tr>
		<td class="bold">Session ID</td>
		<td id="sessionID"><?php echo $id; ?></td>
	</tr>
	<tr>
		<td class="bold">Status</td>
		<td>Expression and Geneset Files have been successfully uploaded and checked for semantic errors. Go ahead, start the Super-Computer.
		</td>
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
</div>		
<?php

			return; // to prevent further ouput
			
			
		} else {
			// Files checked and Error
			
			
		}

		
		
		
	
		if ($ex_error) {
			$expression_error_msg = "<div class='error'>$ex_error</div>\n";
		} else {
			// expression ok
			$expression_ok = true;
		}
		
		if ($gene_error) {
			$geneset_error_msg = "<div class='error'>$gene_error</div>\n";
		} else {
			// gene set ok
			$gene_set_ok = true;
		}
		
	}



	// header
?>
<div id="selectFiles">
<form method="post" action="./" enctype="multipart/form-data">
<table id="uploadtable">

	<tr>
		<td class="bold">Session ID</td>
		<td id="sessionID"><?php echo $id; ?></td>
	</tr>

	<tr>
		<td class="bold">Expression Dataset</td>
		<td>
			<div id="expression">
			<?php if ($expression_ok) { ?>
				<div class='ok'>Expression file uploaded and checked.</div>
			<?php 	} else {
				echo $expression_error_msg; ?>
				<select id="select_expression" name="ex_select">
					<option value="local" selected>Local File</option>
					<option value="remote">Remote File</option>
					<option value="example">Example</option>
				</select>
				
				<div class="local">
					<span id="container">
						<input id="pickfiles" type="submit" value="Choose File" size="40">
						<span id="filelist"></span>
						<span id="uploadProgress"></span>
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
			<?php } ?>
			</div>
		</td>
	</tr>

	<tr>
		<td class="bold">Gene Sets</td>
		<td>
			<div id="geneset">
				<?php echo $geneset_error_msg; 
				
				if (isset($_POST['variations'])) { 
					$variations = $_POST['variations'];
				} else {
					$variations = 8000;
				}
				
				if (isset($_POST['logscale'])) { 
					$logscale = "checked='checked' ";
				} else {
					$logscale = "";
				}
	
				
				?>
				<select id="select_geneset" name="gene_select">
					<option value="local" selected>Local File</option>
					<option value="remote">Remote File</option>
					<option value="example">Example</option>
				</select>
				
				<div class="local">
					<span id="container2">
						<input id="pickfiles2" type="submit" value="Choose File" size="40">
						<span id="filelist2"></span>
						<span id="uploadProgress2"></span>
					</span>
				</div>
				
				<label for="gene_remote" class="remote">http://<input type="url" name="gene_remote" placeholder="url of your geneset" size="40" ></label>
				
				<select class="example" name="gene_example">
					<option value="test.gmt"> gene sets associated with common GO terms </option>
				</select>
				<!--
				<div class="small">
					<a href="#" id="showAdvancedOptions">Advanced Options...</a>
					<div id="advancedOptions">
						<table id="advancedOptionsTable">
							<tr>
								<td><input type="checkbox" name="logscale" value="yes" id="logscale" <?php echo $logscale; ?>></td>
								<td>Convert Expression Value to the log-scale.</td>
							</tr>
							<tr>
								<td><input type="number" name="variations" id="variations" value="<?php echo $variations; ?>" min="1000" max="20000"></td>
								<td>Number of genes after variation filter <em>(default=8000)</em></td>
							</tr>
						</table>
					</div>
				</div>
				-->
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
		<td class="right"><input id="upload" type="submit" value="Upload your Files"></td>
	</tr>
</table>
</form>
</div>
<?php

}



function showInitialUploadForm() {
	showUploadForm(false, false, true);	
}





?>