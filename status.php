<?php
include("/share1/proj/eem/templates/functions.php");

/*
Reminder

define("FILE_UPLOADED",   	1);
define("FILE_CHECKED",    	2);
define("JOB_WAITING",		4);	// qw
define("JOB_RUNNING",		5);	// r
define("JOB_FINISHED", 		6);


*/
// make sure we are coming from mod_rewrite and id directory exists in data
// .htaccess rewrite turns eem.hgc.jp/xxxxx/ into eem.hgc.jp/status.php?id=xxxxx

// Submits job to Supercomputer

$id = $_GET['id'];		// get it via status.php?id=xxxxx !!!
$id_dir_exists = is_dir(DATAPATH . $id);





if (isset($id) && $id_dir_exists) :
	//echo "$id, directory? " . ($id_dir_exists ? "yes" : "no");
	
	
	
	// read status
	$meta = get_meta();	
	

	
	
	// if (isset($_POST['start']) && ($_POST['start']=="yes") && ($meta['expression_status']==FILE_CHECKED) && ($meta['geneset_status']==FILE_CHECKED) && ($meta['status']=='') ) {	
	if (($_POST['start']=="yes") && $meta['status'] < JOB_RUNNING) {	
	
		// submit job
		
		include("header.php"); 
		chdir(DATAPATH . $id);		// change dir to data, so qsub -cwd works...
			
		
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
		
		// href="
		$content = str_replace('href="/', 'href="https://eem.hgc.jp/'.$id.'/', $content);

		//img src="
		$content = str_replace('img src="', 'img src="https://eem.hgc.jp/'.$id.'/', $content);
	
	
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
	$sec = $diff % 60;
	$runningSec = $sec . " Seconds";
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
		<td id="sessionID"><?php echo $id; ?></td>
	</tr>
	<tr>
		<td class="bold">Submitted at</td>
		<td><?php echo $submissionDate; ?></td>
	</tr>
	<tr>
		<td class="bold">Time now</td>
		<td><?php echo date('l jS \of F Y '); ?><span id="timeNow"><?php echo date('H:i:s'); ?></span></td>
	</tr>
	<tr>
		<td class="bold">Running Time</td>
		<td id="runningTime"><?php echo "$runningHrs"."$runningMin"."$runningSec"; ?></td>
	</tr>
	<tr>
		<td class="bold">Register your email?</td>
		<td>
			<input id="userEmail" type="email" name="email" placeholder="<?php
			
			if ($meta["email"]) {
				echo $meta["email"]; 
			} else {
				echo "your@email.com";
			}
			 
			?>">
			<input id="userEmailRegister"type="submit" value="Register">
			<span id ="registedEmail" class="progress"></span>
		</td>
	</tr>

</table>


<script type="text/javascript" charset="utf-8">
	
$(document).ready(function() {

	//var submissionDate = <?php echo $meta['submission_date']; ?>;
	
	var sec = <?php echo $sec; ?>;
	var min = <?php echo $min; ?>;
	var hrs = <?php echo $hrs; ?>;
	var id = "<?php echo $id; ?>";
	
	updateTimer(sec, min, hrs);	
	checkIfFinished(id);	
});

</script>


<?php

}

?>