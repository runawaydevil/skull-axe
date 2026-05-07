<?php
/*
	gera um feed rss dos posts mais recentes

	© 2013 Augusto Campos http://augustocampos.net/ (4.05.2013)
	Licensed under the Apache License, Version 2.0 (the "License"); 
	you may not use this file except in compliance with the License. 
	You may obtain a copy of the License at 
	http://www.apache.org/licenses/LICENSE-2.0 

	Unless required by applicable law or agreed to in writing, software 
	distributed under the License is distributed on an "AS IS" BASIS, 
	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
	See the License for the specific language governing permissions and 
	limitations under the License.	

*/

$tfheader	=file_get_contents("feed.header",true);
$tfitem		=file_get_contents("feed.item",true);
$tffooter	=file_get_contents("feed.footer",true);

function rss_header() {
// processa o cabeçalho
	global $blogparmslist;
	global $tfheader;
	global $ISINDEX;
	$ISINDEX=false;	
	$tfheader=aplica_plugins('feed',$tfheader,'header');
	$header=substparms($tfheader); 
	return $header;
}	


function rss_items() {
// gera entradas de item RSS para os N posts mais recentes
	global $tfitem;
	global $ISINDEX;
	$ISINDEX=false;
	$postsfiles=file(blogparm('INPUTDIR').'posts/'."catalog.txt",FILE_IGNORE_NEW_LINES);
	sort($postsfiles);
	$contaitems=0;
	$feedbody='';
	for ($i=count($postsfiles)-1; $i >=0; $i--) {
		$fpost=$postsfiles[$i];
		unset($GLOBALS['POST']);
		global $POST;
		$k=preg_split('/;;/',$fpost);
		if (blogparm('YEARLY')=="01") {
			$k[1]=preg_replace('/(....\/)..(\/.*)$/',"\\1aBaB\\2",$k[1]);
			$k[1]=preg_replace('/aBaB/',blogparm('YEARLY'),$k[1]);
		}				
		$myimportfile=blogparm('INPUTDIR').'posts/'.$k[1];
		include $myimportfile;
		loadpostvars();		
		$tfitem=aplica_plugins('feed',$tfitem,'item');
		$feedbody.=substpostvars(substparms($tfitem));
		$contaitems++;
		if ($contaitems==blogparm('NUMPOSTSFEED')) return $feedbody;
	}		
	return $feedbody;
}


function rss_footer() {
// processa o rodapé
	global $blogparmslist;
	global $tffooter;
	global $ISINDEX;
	$ISINDEX=false;
	$tffooter=aplica_plugins('feed',$tffooter,'footer');
	$footer=substparms($tffooter);	
	return $footer;
}	

function gera_feed() {
	$feed=rss_header();
	$feed.=rss_items();
	$feed.=rss_footer();
	$nome='feed.xml';
	gravaarquivonaraiz($nome,$feed);	
}

?>