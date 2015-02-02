<?php
include("/share1/proj/eem/templates/functions.php");


// make sure we are coming from mod_rewrite and id directory exists in data
// .htaccess rewrite turns eem.hgc.jp/xxxxx/ into eem.hgc.jp/status.php?id=xxxxx

$id = $_GET['id'];
$id_dir_exists = is_dir(DATAPATH . $id);




if (isset($id) && $id_dir_exists) :
	//echo "$id, directory? " . ($id_dir_exists ? "yes" : "no");
	
	
	
	// read status
	$meta = get_meta();	
	print_r($meta);
	
	if (isset($_POST['start']) && ($_POST['start']=="yes") && ($meta['expression_status']==FILE_CHECKED) && ($meta['geneset_status']==FILE_CHECKED) && ($meta['status']=='') ) {	
		// submit job
		
		include("header.php"); 
		chdir(DATAPATH . $id);		// change dir to data, so qsub -cwd works...
		
		/*
		// preprocess geneset with preprocessing.pl
		$logscale = array_key_exists('logscale', $meta) ? "-l" : "";
		$variatons = array_key_exists('variations', $meta) ? $meta['variations'] : 8000;
		
		$preprocess = "/share1/proj/eem/perl/preprocess.pl -c geneset.gmt $logscale -v $variatons";
		if (DEBUG) echo $preprocess;
		exec($preprocess);
		*/
		
		
		
		//$qsub = "qsub -q web.q -cwd /share1/proj/eem/perl/eemWeb.pl expression.tab geneset.gmt $id 2>&1";
		$qsub = "qsub -q web.q -b y -cwd /share1/proj/eem/perl/eemWeb.pl expression.tab geneset.gmt $id 2>&1";
		
		
		$qsub_response = exec($qsub);
		//echo $qsub_response;
		
		// get job id
		preg_match('/[0-9]+/', $qsub_response, $match);		// Your job 3245364 ("eem.pl") has been submitted
		
		// update meta
		$meta['status'] = JOB_RUNNING;
		$meta['job_id'] = $match[0];
		$meta['submission_date'] = time();
		update_meta($meta);


		showDetails();
		include("footer.php"); 
		
	} elseif ( $meta['status']==JOB_RUNNING ) {
		include("header.php"); 
		
		if (isset($_POST['email'])) {
			$meta['email'] = $_POST['email'];
			update_meta($meta);
		}
		
		/*
		$meta['status'] = JOB_FINISHED;
		update_meta($meta);
		*/
					
		exec("qstat -j " . $meta['job_id'], $qstatArray);
		
		if(DEBUG) {
			echo "<pre>";
			print_r($qstatArray);
			echo "</pre>";
		}
		
		showDetails();
		include("footer.php"); 
	
	} elseif ( $meta['status']==JOB_FINISHED ) {
		
		// Job finished, load results
		
		
		include("header.php"); 
		
		//*$include = include(DATAPATH . "$id/expression_geneset.eemHtml/index.html");
		
		$content = file_get_contents(DATAPATH . "$id/expression_geneset.eemHtml/index.html"); 
		
		// remove datapath from display 
		$content = str_replace(DATAPATH, "", $content);
		$content = str_replace("/expression.tab", "", $content);
	
		// remove noozle head
		$content = explode("<body>", $content);
		$content = $content[1];
		
		// remove noozle foot
		$content = explode("</body>", $content);
		$content = $content[0];
		
		// override background css
		$content = str_replace('<div class="report"', '<div class="report" style="background:none"', $content);		
		
		echo $content;
		
		// show data
		include("footer.php"); 
		
	} else {
		
	}
	
	

	
	
	
	
	
else:
	// Not coming from mod_rewrite
	echo DATAPATH . "$id not found... Please check if the directory exists...";
endif;



function showDetails() {
	global $meta, $id;
	
	// format dates
	date_default_timezone_set('JST');
	$submissionDate = date('l jS \of F Y H:i:s', $meta['submission_date']);
	$now = date('l jS \of F Y H:i:s');
	$diff = time()- $meta['submission_date'];
	$runningSec = $diff % 60 . " Seconds";
	$min = ($diff / 60) % 60;
	$runningMin = $min ? $min . " Minutes " : "";
	$hrs = floor($diff / 3600);
	$runningHrs = $hrs ? $hrs . " Hours " : "";
		
?>
<table id="uploadtable">
	<?php if(DEBUG): ?>
	<tr class="debug">
		<td class="bold">Meta</td>
		<td><pre><?php print_r($meta)?></pre></td>
	</tr>
	<?php endif; ?>
	<tr>
		<td class="bold">Status</td>
		<td><?php 
			switch ($meta['status']) {
				case JOB_WAITING:
					echo "Job waiting in the queue";
					break;
				case JOB_RUNNING:
					echo "Job running";
					break;
				case JOB_FINISHED:
					echo "Job finished";
					break;
			}
			
			?></td>
	</tr>
	<tr>
		<td class="bold">Session ID</td>
		<td><?php echo $id; ?></td>
	</tr>
	<tr>
		<td class="bold">Submitted at</td>
		<td><?php echo $submissionDate; ?></td>
	</tr>
	<tr>
		<td class="bold">Time now</td>
		<td><?php echo $now; ?></td>
	</tr>
	<tr>
		<td class="bold">Running Time</td>
		<td><?php echo "$runningHrs"."$runningMin"."$runningSec"; ?></td>
	</tr>
	<tr>
		<td class="bold">Register your email?</td>
		<td>
		<?php
		// check if email is registered, otherwise ask
		if ($meta["email"]): ?>
			Thanks for registering your email. We'll send you a message, once the computations are done. Or check back and bookmark this page.
		<?php else:?>
			<form method="post" action="<?php echo "."; ?>/">
			<input type="email" name="email" placeholder="your@email.com">
			<input type="submit" value="Register">
			</form>
		<?php endif; ?>
		</td>
	</tr>
</table>

<?php
}






?>