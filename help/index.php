<?php 
$help = true;
include("../header.php"); ?>

<h2>Preparing Files for EEM</h2>

<p>Before using EEM, please prepare the following two files:</p>

<ol>
	<li>
		<div class="bold">Expression dataset</div>
		This file includes features (genes or probes), samples, and an expression value for each 
		feature in each sample. The two file formats are available: GCT, Gene Cluster Text 
		file format (*.gct), and TXT, Text file format for expression dataset (*.txt), which are 
		supported in <a href="http://www.broadinstitute.org/gsea/">GSEA</a>. 
	</li>
	<li>
		<div class="bold">Gene sets</div>
		This file includes one or more predefined gene sets like gene sets from the Molecular 
		Signature Database (<a href="http://www.broadinstitute.org/gsea/msigdb/">MSigDB</a>) or your own defined gene sets. For each gene set, the gene 
		set name, its brief description, and genes in the gene set are given. The file format 
		should be followed by GMT, Gene Matrix Transposed file format (*.gmt).
	</li>
</ol>
<em>For descriptions and examples of each file format, please have a look at the 
<a href="http://www.broadinstitute.org/cancer/software/gsea/wiki/index.php/Data_formats">GSEA file formats</a> documentation.</em>

<br /><br /><br />
<h2>Running EEM</h2>

<h2>1. Uploading Files</h2>

<h3>1.1 Uploading Local Files</h3>
If you have your files on your machine, please choose the 'Local File' option a select your files with the 'Choose File' dialogue. Please make sure, that the individual file size of each file is below 64MB.
<div class="imagewrapper"><img class="shadow" src="images/local.jpg" width="650" /></div>


<h3>1.2 Uploading Remote Files</h3>
If your files already reside on a publicly accessible server, you can choose the 'Remote File' options and specify the URLs of your files.
<div class="imagewrapper"><img class="shadow" src="images/remote.jpg" width="650" /></div>

<h3>1.3 Using Example Files</h3>
If you want to used built-in examples files, please select the 'Example' option.
<div class="imagewrapper"><img class="shadow" src="images/example.jpg" width="650" /></div>

<br><br><br>
<h2>2. Starting the supercomputer</h2>
After everything is checked, we are ready to start EEM on the supercomputer.

<br><br><br>
<h2>3. Registering email for notification</h2>

If you want to receive messages about the completion or your EEM calculations, please register your email. Alternatively you can bookmark the site and come back to it later. However, after 14 days your data will be deleted and the site with your results will no longer be available.
<div class="imagewrapper"><img class="shadow" src="images/job-running.jpg" width="650" /></div>

<br><br><br>
<h2>5. View and download your results</h2>

<div class="imagewrapper"><img class="shadow" src="images/results.jpg" width="650" /></div>

After the calculations are finished, the results can be viewed online or download in a non-server-depended format. The downloaded results can be view locally with a web browser.
For visualizing and presenting the results as a webpage, the Nozzle R Package is used.



<?php include("../footer.php"); ?>