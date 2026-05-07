<?php
/*
	(re)gera os indices em ordem cronologica reversa, de tags, o feed e os posts

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

include blogparm('BLOGINCS').'single.php';
include blogparm('BLOGINCS').'feed.php';


function cover_header($tipo='normal') {
// processa o cabeçalho
	global $blogparmslist;
	global $tiheader;
	global $POST;
	global $ISINDEX;
	global $transfpost;
	$tiheader	=hack_the_header(file_get_contents("header.php",true));	
	$ISINDEX=true;
	setpageicon(substparms("%%BLOGURL%%%%THEMESPATH%%%%THEME%%images/apple-touch-icon.png"));
	setpagedesc(blogparm('BLOGMOTTO'));		
	setpagetitle(blogparm('BLOGTITLE'));
	$pageurl=blogparm('BLOGURL').preg_replace('/'.basename(blogparm('POSTSDIR')).'\//',"/",$nome);
	setpageurl($pageurl);	
	$transfpost['%%POSTTWIT%%']=blogparm('BLOGTITLE')." ".blogparm('BLOGURL');
	$tiheader=aplica_plugins('index',$tiheader,'header',$tipo);
	$header=substpostvars(substparms($tiheader)); 
	return $header;
}	


function cover_feature($tipo='normal') {
// gera o corpo de um post em destaque
	global $tifeat;
	global $POST;	
	global $ISINDEX;
	$tifeat		=try_file_get_contents("capa-feat.php",true);
	if (0===strlen($tifeat)) $tifeat=file_get_contents("capa-post.php",true);
	$ISINDEX=true;
	$tifeat=aplica_plugins('index',$tifeat,'post',$tipo,'feature');
	$firstimage="";
	if (!$nofirstimage) {
		$firstimage=first_image(substpostvars("%%POSTBODY%%"));
	}	
	if (!empty($firstimage)) {
		$tifeat=preg_replace('/%%POSTICON%%/',"$firstimage",$tifeat);
	}	
	$item=substpostvars(substparms($tifeat));
	return $item;
}

function cover_post($tipo='normal') {
// gera o corpo de um post
	global $tipost;
	global $POST;
	global $ISINDEX;
	$tipost=file_get_contents("capa-post.php",true);
	if (0===strlen($tipost)) $tipost=file_get_contents("capa-post.php",true);
	$ISINDEX=true;
	$tipost=aplica_plugins('index',$tipost,'post',$tipo,'post');
	$firstimage="";
	if (!$nofirstimage) {
		$firstimage=first_image(substpostvars("%%POSTBODY%%"));
	}	
	if (!empty($firstimage)) {
		$tipost=preg_replace('/%%POSTICON%%/',"$firstimage",$tipost);
	}	
	$item=substpostvars(substparms($tipost));
	return $item;
}

function cover_news($tipo='normal') {
// gera o corpo de um post curto, de notícia
	global $tinews;
	global $POST;
	global $ISINDEX;
	$tinews		=try_file_get_contents("capa-news.php",true);
	$ISINDEX=true;
	$tinews=aplica_plugins('index',$tinews,'post',$tipo,'news');
	$item=substpostvars(substparms($tinews));
	return $item;
}


function cover_footer($tipo='normal') {
// processa o rodapé
	global $blogparmslist;
	global $tifooter;
	global $POST;
	global $ISINDEX;
	$tifooter	=try_file_get_contents("footer.php",true);
	$ISINDEX=true;
	$tifooter=aplica_plugins('index',$tifooter,'footer',$tipo);
	$footer=substpostvars(substparms($tifooter));	
	return $footer;
}


function rebuild($force=false) {
// gera entradas de capa, tags e feed, e os singles se necessário
	global $tritem;
	global $sparsedebug;
	global $blogparms;
	global $postsfiles;
	global $quickrebuild;	
	global $indexesonly;
	dmsg(blogparm('INPUTDIR').'posts/'."catalog.txt");
	$postsfiles=file(blogparm('INPUTDIR').'posts/'."catalog.txt",FILE_IGNORE_NEW_LINES);
	sort($postsfiles);
	$contaitems=0;
	$contafeats=0;
	$containdexes=0;
	for ($i=count($postsfiles)-1; $i >=0; $i--) {
		if (0 == $contaitems % blogparm('NUMPOSTSCOVER')) {
			$pageurl=blogparm('BLOGURL').preg_replace('/'.basename(blogparm('POSTSDIR')).'\//',"/",$nome);
			setpageurl($pageurl);		
			$item=cover_header();
		}	
		$fpost=$postsfiles[$i];
		unset($GLOBALS['POST']);
		global $POST;
		$k=preg_split('/;;/',$fpost);
		if (count($k)<=5) {
			axe_warning("Linha incompleta no catalog.txt: $fpost");
			continue; // pula essa linha no loop 'for'
		}
		if (blogparm('YEARLY')=="01") {
			$k[1]=preg_replace('/(....\/)..(\/.*)$/',"\\1aBaB\\2",$k[1]);
			$k[1]=preg_replace('/aBaB/',blogparm('YEARLY'),$k[1]);
		}				
   		if ($force || ($contaitens < 2*blogparm('NUMPOSTSCOVER'))) {
			$myimportfile=blogparm('INPUTDIR').'posts/'.$k[1];
			if (!is_file($myimportfile)) {
				axe_error('Não existe o arquivo '.$k[1],E_USER_ERROR);
			}
			
			include $myimportfile;
			loadpostvars();		
		} else {
			loadpostvars_fromcatalog($fpost);
		}	
		if ($contaitems==0 && isset($blogparms['EXIBIRPOPULARES'])>0) {
			$item.=$blogparms['POPULARES'];
		}
		if(strtoupper($POST['POSTICON'])=="NEWS") {
			$item.=cover_news();
		}	
			else if ($contafeats<blogparm('NUMFEATSCOVER')) { 
				$item.=cover_feature();
				$contafeats++;
				if ($contafeats==blogparm('NUMFEATSCOVER')) {
					$item.=quadro_destaques();
				}
			}	
				else $item.=cover_post();
				if ($contaitems == 1) {
					$item.=$blogparms['MIDAD'];
				}
		if (!$indexesonly) {
			if ($contaitems < blogparm('NUMPOSTSCOVER')*2) {
				gera_single($k[1],true,false,$contaitems); // sempre força a regeração dos posts das primeiras 2 páginas do índice
			} else if ($force || !isset($k[6]) || (time()-$k[6]<600)) {
				gera_single($k[1],$force,false,$contaitems);
			}
		}		
		// para a lista de artigos recentes:
		if ($contaitems<6) {
			$postsrecentes.=substpostvars(substparms("<li><a href=\"%%BLOGURL%%%%POSTSURLPREFIX%%%%POSTURL%%\">%%POSTTITLE%%</a>\n"));
		}	
		
		$contaitems++;

		if ($contaitems==6) grava_postsrecentes($postsrecentes);
		if ((0 == $contaitems % blogparm('NUMPOSTSCOVER')) || ($i==0)) {
			$numindex=$containdexes+1;
			$previndex=$numindex-1;
			$proxindex=$numindex+1;
			$prevlink='index-'.$previndex.".html";
			$proxlink='index-'.$proxindex.".html";
			if ($containdexes==1) $prevlink="index.html";
			$navlinks="";
			if ($i>0) $navlinks='<span class="next"><a href="'.blogparm('BLOGURL').$proxlink.'" rel="next">&larr; Mais antigos</a></span>';
			if($containdexes>0) $navlinks.='<span class="prev"><a href="'.blogparm('BLOGURL').$prevlink.'" rel="prev">Mais recentes &rarr;</a></span>';
			setnavlinks($navlinks);
			$item.=cover_footer();
			setpageicon(substparms("%%BLOGURL%%%%THEMESPATH%%%%THEME%%images/apple-touch-icon.png"));			
			$item=preg_replace('/@@PAGEICON@@/',blogparm('PAGEICON'),$item);		
			$nome="index-$numindex.html";
			if ($containdexes==0) $nome="index.html";
			$pageurl=blogparm('BLOGURL').preg_replace('/'.basename(blogparm('POSTSDIR')).'\//',"/",$nome);
			$pageurl=preg_replace('/\/index.html/',"/",$pageurl);
			setpageurl($pageurl);
			$sparsedebug=1;
			gravaarquivonaraiz($nome,$item);
			$sparsedebug=0;
			$containdexes++;
			$item="";
		}	
		if ((true===$quickrebuild) && !$force && ($containdexes>10)) break;
	}
	gera_feed(); // feed RSS
	if ((false===$quickrebuild) || $force) {
		rebuild_tags($force);
		monthly_index();
		sitemap();
	}	
	$tiheader=aplica_plugins('index',$force,'rebuild',$quickrebuild);
	return $item;
}
?>