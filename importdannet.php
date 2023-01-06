<?php 

// // // // // // // // // // // // // // // // // // // // // 
// 
// importcsvtosql reads csv-files and inserts it into sql. 
// Author: Jeppe Bundsgaard <jeppe@bundsgaard.net>
// Import to MySQL of DanNet's WordNet-database. See DanNet's README.txt in the DanNet-2.2 folder
// The DanNet-csv-files have been converted into utf-8 by hand
// First: Create the tables from dannet.sql
// The relations table has been expanded to differentiate between relations in DanNet and relations to dummies and WordNet
// 



$user="dannet";
$password="6THuooSyKOhyQMG3$";
$mysqli = new mysqli("localhost", $user, $password, "dannet");
// importcsvtosql($mysqli,"./DanNet-2.2/dummies-utf8.csv","dummies");
// importcsvtosql($mysqli,"./DanNet-2.2/synsets-utf8.csv","synsets");
// importcsvtosql($mysqli,"./DanNet-2.2/words-utf8.csv","words");
importcsvtosql($mysqli,"./DanNet-2.2/wordsenses-utf8.csv","wordsenses");
// importcsvtosql($mysqli,"./DanNet-2.2/relations-utf8.csv","relations",20,$convertfunctions=array(3=>"relationdiff"));
function importcsvtosql($mysqli,$file,$table,$firstline=0,$convertfunctions=array(),$maxcharacters=10000) {
	$q="SHOW COLUMNS FROM ".$table;
	$r=$mysqli->query($q);
	$columns=array_map(function($i) {return $i["Field"];},$r->fetch_all(MYSQLI_ASSOC));
	
	$maxrows=INF;
	$numcol=count($columns);
	echo "Reading: <b>".$file."</b><br>";
	$row=0;
	if (($handle = fopen($file, "r")) !== FALSE) {
		while (($data = fgetcsv($handle, $maxcharacters, "@",)) !== FALSE and $row<($maxrows+$firstline)) {
			if($row>=$firstline) {
				foreach($convertfunctions as $col=>$fnc) {
					$data=$fnc($data);
				}
				if(count($data)>$numcol) {
					if($data[$numcol]!="")
						echo "Warning: Too much data: ".implode("; ",array_slice($data,$numcol))."<br>";
					$data=array_slice($data,0,$numcol);
				}
				$q='insert IGNORE into '.$table.' ('.
					implode(",",array_map(function($c) { return '`'.$c.'`';},$columns)).
					') VALUES ('.
					implode(",",array_map(function($d) use ($mysqli,$convertfunctions,$c) { 
						return '"'.$mysqli->real_escape_string($d).'"';
						
					},$data)).
					')';
				echo $q."<br>";
				$mysqli->query($q);
			}
			$row++;
		}
		fclose($handle);
	}
}
function relationdiff($data) {
	$d=$data[3];
	$target=(strpos($d,"ENG20")===0?"wordnet":(strpos($d,"%")?"dummies":"dannet"));
	$value=$target=="dannet"?$d:"";
	$othervalue=$target=="dannet"?"":$d;
	$values=array($target,$value,$othervalue);
	return array_merge(array_slice($data,0,3),$values,array_slice($data,4));
}
?>
