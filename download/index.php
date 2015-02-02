<?php 
$download = true;
include("../header.php"); ?>

<p>A R-script of R-EEM  is provided <a title="http://www.hgc.jp/~niiyan/REEM/data/REEM1.0.R" href="http://www.hgc.jp/~niiyan/REEM/data/REEM1.0.R">here</a>. <br /></p>
<br />
An input example (Niida et al., 2009) is also provided:
<ul>
	<li><a href="http://eem.hgc.jp/data/input/breastMiller.tsv">Breast tumor microarray data</a> prepared from <a class="class1" title="http://www.ncbi.nlm.nih.gov/geo/query/acc.cgi?acc=GSE3494" href="http://www.ncbi.nlm.nih.gov/geo/query/acc.cgi?acc=GSE3494">a GEO data set</a></li>
	<li><a href="http://eem.hgc.jp/data/input/PWM.gmt">A TF target gene set library</a> predicted based on TRANSFAC PWMs</li>
</ul>

An output example from R-EEM analysis using these input data is provided <a title="http://www.hgc.jp/~niiyan/REEM/data/output/index.html" href="http://www.hgc.jp/~niiyan/REEM/data/output/index.html">here</a>.</p>

<?php include("../footer.php"); ?>
