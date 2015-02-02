<?php
echo 'upload_max_filesize = ' . intVal(ini_get('upload_max_filesize')). " MB<br>\n";
echo 'upload_max_filesize = ' . intVal(ini_get('upload_max_filesize')) * 1014  . " KB<br>\n";
echo 'upload_max_filesize = ' . intVal(ini_get('upload_max_filesize')) * 1014 * 1024 . " B<br>\n";

?>
