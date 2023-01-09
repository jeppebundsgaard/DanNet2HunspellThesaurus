<?php
// get contents of a file into a string
$idx=explode("\n",file_get_contents("/home/jeppe/www/DanNet2HunspellThesaurus/thesaurus/th_da_DK.idx"));

$filename = "/home/jeppe/www/DanNet2HunspellThesaurus/thesaurus/th_da_DK.dat";
$handle = fopen($filename, "r");

foreach($idx as $i) {
	$id=explode("|",$i);
	if($id[1]) {
		fseek($handle, $id[1]);
		$next = fread($handle, 100);
		echo "\n<br><b>".$id[0].":</b> ".$next;
		// if($id[1]>10000000) exit;
	}
}
fclose($handle);
?>
