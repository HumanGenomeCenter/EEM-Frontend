<pre>
<?php

echo "<b>whoami </b>";
system("whoami");

echo "<b>pwd </b>";
system("pwd");

echo "<b>perl -v </b>";
system("perl -v");

echo "<b>which java </b>";
system("which java");


echo "<b>java -version </b>";
system('java -version', $returnValue);
print_r( $returnValue );



?>
</pre>
