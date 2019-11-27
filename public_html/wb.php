<?php
$fname = uniqid() . ".wav";
$sname = "library.json";

$inputHandler = fopen('php://input', "r");
$fileHandler = fopen($fname, "w+");

while(!feof($inputHandler)){
	fwrite($fileHandler, fread($inputHandler, 1024));
	
	flush();
}
fclose($fileHandler);
fclose($inputHandler);

$out = proc_open("sr.exe " . $fname . " " . $sname,
[
	1 => array('pipe', 'w'),
	2 => array('pipe', 'w')
],
$o);

while (!feof($o[1])) {
	echo htmlspecialchars(fread($o[1], 1024));
}

while (!feof($o[2])) {
	echo htmlspecialchars(fread($o[2], 1024));
}

proc_close($out);
unlink($fname);
?>