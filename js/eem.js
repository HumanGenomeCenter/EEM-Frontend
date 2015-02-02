var ajaxURL = "https://eem.hgc.jp/ajax.php";
var id;

var eem = {};

// EEM Log Function
eem.debug = true;
eem.log = function(msg) {
	if (eem.debug) {
		console.log(msg);
	}
}


$(document).ready(function(){
	
	// Get ID
	id = $('#sessionID').html();
	
	// Toogle Advanced Options
	$("a#showAdvancedOptions").click(function(e) {
		$("#advancedOptions").slideToggle(400);
		e.preventDefault();		
	});
		
	// EEM
	
	// Expression
	
	// Selection
	$('#expression > #select_expression').change(function(){
		
		$("#expression .progress").html("");		// reset all progress fields
		$("#checkingMessage").html("");				// reset checking message msg
		
		// hide all...
		$('#expression .local').hide();
		$('#expression .remote').hide();
		$('#expression .example').hide();
		  
		// ... only show selected
		$('#expression .'+$(this).val()).show();
	});
	

	// Expression Local Upload managed by pupload
	
	// Expression Remote Upload
	$("#expression > .remote > #use").on('click', function(e) {
		$("#expressionStatus").html("");
		var file = $("#ex_remote").val();
		var message = $("#expressionRemoteUploadProgress");
		var name = "expressionRemoteUpload";
		submitUploadRequest(file, message, name);
		e.preventDefault();
	});

	// Expression Example Upload
	$("#expression > .example > #use").on('click', function(e) {
		$("#expressionStatus").html("");
		var file = $("#ex_example").val();
		console.log(file);
		var message = $("#expressionExampleUploadProgress");
		var name = "expressionExampleUpload";
		submitUploadRequest(file, message, name);
		e.preventDefault();
	});


	// Geneset
	
	// Geneset Selection
	$('#geneset > #select_geneset').change(function(){
		
		$("#geneset .progress").html("");		// reset all progress fields
		$("#checkingMessage").html("");			// reset checking message msg
		
		// hide all...
		$('#geneset .local').hide();
		$('#geneset .remote').hide();
		$('#geneset .example').hide();
		
		// ... only show selected
		$('#geneset .'+$(this).val()).show();	
	});

	// Geneset Remote Upload
	$("#geneset > .remote > #use").on('click', function(e) {
		var file = $("#gene_remote").val();
		var message = $("#genesetRemoteUploadProgress");
		var name = "genesetRemoteUpload";
		submitUploadRequest(file, message, name);
		e.preventDefault();
	});

	// Geneset Example Upload
	$("#geneset > .example > #use").on('click', function(e) {
		var file = $("#gene_example").val();
		console.log(file);
		var message = $("#genesetExampleUploadProgress");
		var name = "genesetExampleUpload";
		submitUploadRequest(file, message, name);
		e.preventDefault();
	});






	// Preprocess
	
	// Preprocess Selection
	$('#preprocess  #select_preprocess').change(function(){
		
		$("#preprocess .progress").html("");		// reset all progress fields
		$("#checkingMessage").html("");			// reset checking message msg
		
		// hide all...
		$('#preprocess .local').hide();
		$('#preprocess .remote').hide();
		$('#preprocess .example').hide();
		
		// ... only show selected
		$('#preprocess .'+$(this).val()).show();	
	});
	
	// Preprocess Remote Upload
	$("#preprocess .remote > #use").on('click', function(e) {
		var file = $("#preprocess_remote").val();
		var message = $("#preprocessRemoteUploadProgress");
		var name = "preprocessRemoteUpload";
		submitUploadRequest(file, message, name);
		e.preventDefault();
	});

	// Preprocess Example Upload
	$("#preprocess .example > #use").on('click', function(e) {
		var file = $("#preprocess_example").val();
		var message = $("#preprocessExampleUploadProgress");
		var name = "preprocessExampleUpload";
		submitUploadRequest(file, message, name);
		e.preventDefault();
	});
	
	
	// Preprocess Submit with Button
	$("#advancedOptionsTable #preprocessButton").on('click', function(e) {
		submitPreprocessRequest();
		e.preventDefault();
	});
	

	$("#start").on('click', function(e) {
		console.log("start clicked");
		// do not e.preventDefault(); Submitting the form to /xxxxx starts the super computer
	});


	// register Email
	$("#userEmailRegister").on('click', function(e) {
		var email = $("#userEmail").val();
		console.log(email);
		
		var name = "registerEmail";
		var message = $("#registedEmail");
		message.html("Registering Email...");
		
		$.post(ajaxURL, {'name':name,'id':id, 'userEmail':email}, function(r) {
			// catch JSON parse error
			console.log(r);
			
			try {
				var response = JSON.parse(r);
				
				if (response.result) {
					message.html("Email registered.");					// ok
				} else {
					message.html("Registering Email Failed.");		// error
				}
				
			} catch(error) {
				message.html("Error: Preprocessing Failed.");					// ok
				return false;
			}
			
			
			
			
		});
		e.preventDefault();
	});


});


var submitPreprocessRequest = function() {
	console.log("Submitting Preprocesssing Request...");
	
	// enable "Preproces Expression..." Button
	$("#advancedOptionsTable #preprocessButton").attr('disabled', false);
	
	$("checkingMessage").html("");
	
	var message = $("#expressionStatus");
	var name = "preProcessExpression";
	var logscale = $("#preprocess #logscale").attr('checked') ? true : false;
	var variations = $("#preprocess #variations").val();
	var type = $("#preprocess #select_preprocess").val();
	//console.log(message, name, logscale, variations);
	
	message.removeClass().addClass("progress");
	message.show();
	message.html("Preprocessing...");
	
	$.post(ajaxURL, {'name':name,'id':id, 'logscale':logscale, 'variations':variations, 'type':type}, function(r) {
		// catch JSON parse error
		console.log(r);
		
		try {
			var response = JSON.parse(r);
			
			if (response.result) {
				submitFileCheck();		// check files...
				
				message.html("Preprocessing OK.");					// OK
				message.removeClass().addClass("success");
				message.delay(1000).fadeOut(1000);

			} else {
				message.removeClass().addClass("failure");
				message.html("Preprocessing Failed.");				// NG
			}
			
			
		} catch(error) {
			message.html("JSON-RPC Parse Error.");							// Parse Error
			return false;
		}
		
	
	});	
}


// Submit AJAX Request to check expression and geneset against each other
// Has to be outside (ready) to be access from the Local Uploader
var submitFileCheck = function() {
	// submit files check after expression preprocessing and geneset upload
	
	$("#start").attr('disabled', true);	// disable start button
	
	
	var message = $('#checkingMessage');
	console.log("submitFileCheck");

	// AJAX check files
	message.html("");
	message.removeClass().addClass("progress");		// remove all classes, add progress
	
	$.post(ajaxURL, {'name':'fileCheck','id':id}, function(r) {
		console.log("file check response", r);
		try {
			var response = JSON.parse(r);
			if (response.result) {
				// ok

				console.log("startEEM: " + response.result.startEEM);

				if (response.result.startEEM) {
					message.html("Files checked and OK.");
					message.removeClass().addClass("success");

					$("#start").attr('disabled', false);	// enable start button
				} else {

				}

			} else {
				// error
				message.html("Files are inconsistent. Please check them again.");
				message.removeClass().addClass("failure");

			}
			
		} catch(error) {
			message.html("Parse Error: Could not parse response...");					// ok
			return false;
		}
		
		
	});

}






var submitUploadRequest = function(file, message, name) {
	
	// No URL input
	if (file.length===0) {
		message.html("Please check your URL...");
		return false;
	}		
	message.html("0%");
	
	console.log( file, name);
	
	$.post(ajaxURL, {'name':name,'id':id, 'file':file}, function(r) {
		// catch JSON parse error
		console.log(r);
		try {
			var response = JSON.parse(r);
						
			// File uploaded
			if (response.result) {
				
				message.removeClass().addClass("success");
				message.html("100%");					// ok
				
				console.log("submitUploadRequest", name);
				
				
				if (name==="expressionRemoteUpload" || name==="expressionExampleUpload" || name==="preprocessRUpload" || name==="preprocessExampleUpload") {
					submitPreprocessRequest();		// submitFileCheck() is also called after preprocessing...
				} else if(name==="genesetRemoteUpload" || name==="genesetExampleUpload") {
					submitFileCheck();
				}

			} else {
				console.log(response);
				message.removeClass().addClass("failure");
				message.html("Upload Failed.");		// error
			}
			
		} catch(error) {
			message.html("Parse Error: Please check your URL...", file, message, name);					// ok
			return false;
		}
				
	});
}



var updateTimer = function(sec, min, hrs) {
	setInterval(function(){
		sec++;

		if (sec >=60) {
			sec = 0;
			min++;
		}
		
		if (min >=60) {
			min = 0;
			hrs++;
		}
		
		var output = sec + " Seconds";
		if (min > 0 || hrs > 0) output = min + " Minutes " + output;
		if (hrs > 0) output = hrs + " Hours " + output;
		$("#runningTime").html(output);

	},1000);
}


var checkIfFinished = function(id) {
	// Call every 10 seconds, check for results
	setInterval(function(){
		console.log("Checking Results...");
		$.post(ajaxURL, {'name':'checkIfFinished','id':id}, function(r) {
			try {
				var response = JSON.parse(r);
				if (response.result) {
					console.log("Results ready... reloading.");
					location.reload(true);	// reload page
				} else {
					console.log("Still computing, please wait a bit longer.");
				}
			} catch(error) {
				return false;
			}
		});
	},10000);
}







