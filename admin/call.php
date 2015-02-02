<pre>
<?php

system("whoami");

system("cd /share1/proj/eem/dataTmp/xlelr/");
system("rm eemWeb.pl*");

//system("perl /share1/proj/eem/perl/checkFormat.pl /share1/proj/eem/dataTmp/xlelr/expression.tab /share1/proj/eem/dataTmp/xlelr/geneset.gmt", $returnValue);
system("perl /share1/proj/eem/perl/checkFormat.pl /share1/proj/eem/dataTmp/xlelr/expression.tab /share1/proj/eem/dataTmp/xlelr/geneset.gmt xlelrr", $returnValue);


echo "return value:";
echo $returnValue;
echo "\nEOF";


/*
	perl /share1/proj/eem/perl/checkFormat.pl expression.tab geneset.gmt
	[w3eem@ruby xlelr]$ pwd
	/share1/proj/eem/dataTmp/xlelr
	
*/


?>
</pre>