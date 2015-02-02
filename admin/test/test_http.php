<?php


$urlf = "http://eem.hgc.jp/data/input/breastMiller.tsv";
$url = "eem.hgc.jp/data/input/breastMiller.tsv";


echo checkURL($urlf);


function checkURL($url) {
	if ( (substr($url, 0, 7)=='http://') || (substr($url, 0, 8)=='https://') ) {
		return $url;
	} 
	return 'http://' . $url;
}




	





?>