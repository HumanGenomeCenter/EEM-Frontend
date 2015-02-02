<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>EEM: Extraction of Expression Modules</title>
	<link rel="stylesheet" type="text/css" media="screen" href="/style.css" />
	<script type="text/javascript" charset="utf-8" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script type="text/javascript" charset="utf-8" src="https://eem.hgc.jp/js/eem.js?<?php echo rand(100000,999999)?>"></script>
</head>
<body>

<div id="line"></div>
<!--<a href="https://eem.hgc.jp/citation/"><img id="cite" src="/images/eem-citation.png"></a>-->

<div id="container">
<div id="page">

<div id="top">
	<a href="http://www.u-tokyo.ac.jp/index_e.html"><img src="/images/u-tokyo_logo_trans.png" width="160" height="23"></a>
	<a href="http://www.ims.u-tokyo.ac.jp/imsut/en/"><img src="/images/ims_logo_trans.png" width="232" height="23"></a>
	<a href="http://www.hgc.jp/english/"><img src="/images/hgc_logo_trans.png" width="226" height="23"></a>
</div>

<div id="headline">
	<!-- <a href="/"><img src="/images/eem.png" width="594" height="148" border="0"></a> -->
	<a href="/">
	<h1>EEM <span><?php if ( ($meta['geneset_status']) && ($meta['expression_status']) ) echo $id; ?></span></h1>
	<h1 id="tagline">Extraction of Expression Modules</h1>
	</a>
</div>

<div id="navigation">
	<ul>
		<li><a href="/">Top</a></li>
		<li><a href="/help/"<?php if ($help) { echo ' class="selected"'; }?>>Help</a></li>
		<li><a href="/faq/"<?php if ($faq) { echo ' class="selected"'; }?>>FAQ</a></li>
		<li><a href="/publications/"<?php if ($publications) { echo ' class="selected"'; }?>>Publications</a></li>
		<li class="right"><a href="/contact/"<?php if ($contact) { echo ' class="selected"'; }?>>Contact</a></li>
	</ul>
</div>

<?php 


if ($meta['status']==JOB_FINISHED):		// don't show when noozle outputs its data 		
	// close 'container' and 'line' tags
	// Add download link
?>
<div id="downloadLink">
	Download results for local viewing: <a href="https://eem.hgc.jp/<?php echo "$id/"; echo $meta['zip']; ?>"><?php echo $meta['zip']; ?></a>
</div>

</div>
</div>
<?php else: ?>
<div id="content">
<?php endif; ?>
