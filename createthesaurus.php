<?php 
// // // // // // // // // // // // // // // // // // 
// 
// Converts dannet tables into a thesaurus that can be used in LibreOffice and other programs that reads .dat and .idx-files.
// Author: Jeppe Bundsgaard <jeppe@bundsgaard.net>
// Data layout of the files .dat and .idx are explained in the file data_layout.txt from https://github.com/hunspell/mythes by Kevin Hendricks kevin.hendricks@sympatico.ca
//

//Settings
$outfiles="./thesaurus/th_da_DK";
$idx=$dat="UTF-8\n";

$user="dannet";
$password=file_get_contents(".passw");

$mysqli = new mysqli("localhost", $user, $password, "dannet");

# Get all words in alphabetic order.
$q='
select 
	form, 
	w.word_id, 
	group_concat(ws.synset_id SEPARATOR ",") as synsets , 
	group_concat(r1.value SEPARATOR ",") as similarsynsets,
	group_concat(r2.value SEPARATOR ",") as generalsynsets 
from 
	words w 
	left join wordsenses ws on w.word_id=ws.word_id 
	left join relations r1 on r1.synset_id=ws.synset_id AND r1.name2 LIKE "near_synonym" 
	left join relations r2 on r2.synset_id=ws.synset_id AND r2.name2 LIKE "has_hyperonym" 
where w.word_id!="NONE-NONE" 
GROUP BY w.word_id order by form';// LIMIT 2000;';
$r=$mysqli->query($q);



$idx.=$r->num_rows."\n";

while($w=$r->fetch_assoc()) {
	# Get all meanings of the word, and all synonyms of each meaning
	if(!$w["similarsynsets"]) $w["similarsynsets"]="-1";
	if(!$w["generalsynsets"]) $w["generalsynsets"]="-1";
	$q1='
	select 
		concat_ws("|",
			concat("(",ELT(FIELD(pos,"Adjective","Noun","Verb"),"tillægsord","navneord","verbum"),")"),
			group_concat(DISTINCT CONCAT(w.form,
				IF(synset_id IN  ('.$w["similarsynsets"].')," (nærliggende betydning)",
				IF(synset_id IN  ('.$w["generalsynsets"].')," (overbegreb)",""))
			) SEPARATOR "|")
			) as syns 
	from words w left join wordsenses ws on w.word_id=ws.word_id 
	where ws.word_id NOT IN ("'.$w["word_id"].'","NONE-NONE") and synset_id IN 
		('.$w["synsets"].') or synset_id IN ('.$w["similarsynsets"].') or synset_id IN ('.$w["generalsynsets"].') GROUP BY synset_id ORDER BY FIELD(synset_id,'.$w["synsets"].','.$w["similarsynsets"].','.$w["generalsynsets"].')';
	try {
		$r1=$mysqli->query($q1);
	}
	catch(Exception $objException) {
		throw new Exception(sprintf('MySQL error in %s',$q1));
	}  
	
	$first=true;
	while($row=$r1->fetch_assoc()) { # For each synset we give its part of speech, and the synonyms
		if($row["syns"]=="") break;
		if($first) { // The first row is the word and info
			$first=false;
			$word=$w["form"];
			$bytepos=mb_strlen($dat, '8bit');
			$idx.=$word."|".$bytepos."\n"; // The .idx consist of a word and it's byte-position
			$dat.=$word."|".$r1->num_rows."\n"; //The word and the number of meanings
		} 
		$dat.=$row["syns"]."\n";
	}
}
file_put_contents($outfiles.".dat",$dat);
file_put_contents($outfiles.".idx",$idx);
echo "Done";//\n".$dat." \n\nAnd\n".$idx;
