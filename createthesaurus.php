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

$mysqli = new mysqli("localhost", $user, trim($password), "dannet");
$mysqli->query("SET @@group_concat_max_len = 8192");
# Get all words in alphabetic order.
# Leave synset_id 20633 out - it is TOP inserted by DanNet.
$q='
select 
	form, 
	w.word_id, 
	group_concat(DISTINCT ws.synset_id SEPARATOR ",") as synsets , 
	group_concat(DISTINCT r1.value SEPARATOR ",") as similarsynsets,
	group_concat(DISTINCT r2.value SEPARATOR ",") as oversynsets, 
	group_concat(DISTINCT r3.synset_id SEPARATOR ",") as undersynsets 
from 
	words w 
	left join wordsenses ws on w.word_id=ws.word_id 
	left join relations r1 on r1.synset_id=ws.synset_id AND r1.name2 LIKE "near_synonym" and r1.synset_id!=20633 
	left join relations r2 on r2.synset_id=ws.synset_id AND r2.name2 LIKE "has_hyperonym" and r2.synset_id!=20633 
	left join relations r3 on r3.value=ws.synset_id AND r3.name2 LIKE "has_hyperonym" and r3.value!=20633 
where NOT REGEXP_LIKE(form,"[ (]") and ws.synset_id!=20633 and w.word_id!="NONE-NONE"'. //form LIKE "blive" './/
' GROUP BY w.word_id order by form';// LIMIT 2000;';
$r=$mysqli->query($q);
// echo $q;


$idx.=$r->num_rows."\n";

while($w=$r->fetch_assoc()) {
	# Get all meanings of the word, and all synonyms of each meaning
	// print_r($w);
	if(!$w["similarsynsets"]) $w["similarsynsets"]="-1";
	if(!$w["oversynsets"]) $w["oversynsets"]="-1";
	if(!$w["undersynsets"]) $w["undersynsets"]="-1";
	$q1='
	select 
		concat_ws("|",
			concat("(",ELT(FIELD(pos,"Adjective","Noun","Verb"),"tillægsord","navneord","verbum"),") "'.
				', SUBSTRING(REPLACE(REGEXP_SUBSTR(s.gloss,".*?(\\\\.\\\\.\\\\.|\\\\()"),"(",""),1,30)'.
			'),
			group_concat(DISTINCT CONCAT(w.form,
				concat(
					IF( register!="",concat(" (",register,")"),""),
					IF( ws.synset_id IN  ('.$w["similarsynsets"].')," (nærliggende betydning)",
						IF(ws.synset_id IN  ('.$w["oversynsets"].')," (overbegreb)",
						IF(ws.synset_id IN  ('.$w["undersynsets"].')," (underbegreb)",""))
					)
				)
			) SEPARATOR "|")
			) as syns 
	from words w left join wordsenses ws on w.word_id=ws.word_id left join synsets s on ws.synset_id=s.synset_id
	where ws.word_id NOT IN ("'.$w["word_id"].'","NONE-NONE") and (ws.synset_id IN 
		('.$w["synsets"].') or ws.synset_id IN ('.$w["similarsynsets"].') or ws.synset_id IN ('.$w["oversynsets"].') or ws.synset_id IN ('.$w["undersynsets"].')) GROUP BY ws.synset_id ORDER BY FIELD(ws.synset_id,'.$w["synsets"].','.$w["similarsynsets"].','.$w["oversynsets"].','.$w["undersynsets"].')';
		
		// Synonyms will sometimes have another definition than the main word. Tried to show the main definition, but not the word itself (otherwise the word would be synonym for itself), but that will leave a number of empty lines:
		// if(w.form LIKE "'.$w["form"].'","",)
		
		
	try {
		$r1=$mysqli->query($q1);
	}
	catch(Exception $objException) {
		throw new Exception(sprintf('MySQL error %s in %s',$mysqli->error,$q1));
	}  
	
	$first=true;
	while($row=$r1->fetch_assoc()) { # For each synset we give its part of speech, and the synonyms
		// print_r($row);
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
echo "Done\n".$dat." \n\nAnd\n".$idx;
