# DanNet2HunspellThesaurus
LibreOffice Thesaurus created from DanNet's wordnet structure. Might be useful for other wordnets as well.

## DanNet
DanNet is a wordnet for Danish. Developed by Pedersen, Bolette S. Sanni Nimb, Jørg Asmussen, Nicolai H. Sørensen, Lars Trap-Jensen and Henrik Lorentzen. 

Project page: https://cst.ku.dk/projekter/dannet/. 

The CSV-files from DanNet is available in the DanNet-2.2-folder.

## Scripts in this repository
The file `dannet.sql` consists of the database structure. Import it into a database `dannet`. Create the user `dannet` and give it access to read and write in the database. Put the password in the file .passw in the root folder.

The file `importdannet.php` imports the DanNet csv-files (converted manually to utf-8 to avoid csv-problems). See the script for a few tweaks.

The file `createthesaurus.php` converts dannet tables into a thesaurus that can be used in LibreOffice and other programs that reads .dat and .idx-files. The files are saved in the `thesaurus` folder. Remember to give the www-data user read and write access.

Data layout of the files `.dat` and `.idx` are explained in the file `data_layout.txt` from https://github.com/hunspell/mythes by Kevin Hendricks kevin.hendricks@sympatico.ca.

## Include in LibreOffice OXT-files
Unpack the .oxt-file (it is a zip-archive), add or replace the two thesaurus files: `th_da_DK.dat` and `th_da_DK.idx`. Zip it back again.

## Author
Jeppe Bundsgaard, jeppe@bundsgaard.net.

Take a look at our other language project: Stavekontrolden. A webbased interactive system to develop spellcheck libraries, https://github.com/jeppebundsgaard/stavekontrolden

## References
Pedersen, Bolette S. Sanni Nimb, Jørg Asmussen, Nicolai H. Sørensen, Lars Trap-Jensen og Henrik Lorentzen (2009). DanNet – the challenge of compiling a WordNet for Danish by reusing a monolingual dictionary. Lang Resources & Evaluation 43:269–299.
