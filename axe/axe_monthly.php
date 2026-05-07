<?php
/*
	gera os índices mensais de posts

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

function monthly_header($ano=0,$mes=0,$tipo='normal') {
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
	if ($ano==0) $header.="<h1>Histórico de posts</h1><p><br>";
	else $header.="<h1>Posts publicados em $mes/$ano</h1><p><a href='archive.html'>Retornar ao histórico de posts</a><br>";
	return $header;
}	


function monthly_post($tipo='normal') {
// gera o corpo de um post
	global $tipost;
	global $POST;
	global $ISINDEX;
	$tipost		=file_get_contents("monthly-post.php",true);
	if (0===strlen($tipost)) $tipost=file_get_contents("capa-post.php",true);
	$ISINDEX=true;
	$tipost=aplica_plugins('index',$tipost,'post',$tipo,'post');
	$item=substpostvars(substparms($tipost));
	return $item;
}

function monthly_arch($tipo='normal') {
// gera o corpo de um post
	global $tipost;
	global $POST;
	global $ISINDEX;
	$tipost		=file_get_contents("monthly-arch.php",true);
	if (0===strlen($tipost)) $tipost=file_get_contents("capa-post.php",true);
	$ISINDEX=true;
	$tipost=aplica_plugins('index',$tipost,'post',$tipo,'post');
	$item=substpostvars(substparms($tipost));
	return $item;
}


function monthly_footer($tipo='normal') {
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


function monthly_index() {
	global $blogparms;
	global $transfpost;	
	$postsfiles=file(blogparm('INPUTDIR').'posts/'."catalog.txt",FILE_IGNORE_NEW_LINES);
	sort($postsfiles);
	$contaitems=0;
	$item='';
	$anomesproc="";
	$anoproc="";
	$mesproc="";
	$dataproc="";
	$archive=monthly_header(0,0);	
	$anoprox=date("Y");
	$anoarch=$anoprox;
	for ($i=count($postsfiles)-1; $i >=0; $i--) {
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
		loadpostvars_fromcatalog($fpost);	
		$anomes=date("Y/m",strtotime(substpostvars("%%POSTTIME%%")));
		$ano=date("Y",strtotime(substpostvars("%%POSTTIME%%")));
		$mes=date("m",strtotime(substpostvars("%%POSTTIME%%")));
		if ($anomes!=$anomesproc) {
			// fecha o mês anterior
			if (!empty($item)) {
				$item.=monthly_footer();
				setpageicon(substparms("%%BLOGURL%%%%THEMESPATH%%%%THEME%%images/apple-touch-icon.png"));			
				$item=preg_replace('/@@PAGEICON@@/',blogparm('PAGEICON'),$item);						
				gravaarquivonaraiz("archive-$anoproc-$mesproc.html",$item);
				if (!empty($anoprox) && ($imprimiuano!=1)) {
					setmonthlyindexdate($anoprox);
					$anoprox="";
					$imprimiuano=1;
				} else {
					setmonthlyindexdate("");
					$imprimiuano=0;
				}
				if ($ano!=$anoarch) {
					$anoarch=$ano;
					$anoprox=$ano;					
				}	
				setmonthlyindextitle("<a href='archive-$anoproc-$mesproc.html'>Posts publicados em $mesproc/$anoproc</a>");
				$archive.=monthly_arch();
				$item="";
			}
			// cabeçalho de um novo mês
			$item=monthly_header($ano,$mes);
			decho("Gravando archive-$ano-$mes.html");
			$anomesproc=$anomes;
			$anoproc=$ano;
			$mesproc=$mes;
		}
		$data=substpostvars('%%POSTDATE%%');
		if ($data != $dataproc) {
			setmonthlyindexdate($data);
			$dataproc=$data;
		} else 	setmonthlyindexdate("");
		$item.=monthly_post();		
	}
	if (!empty($item)) {
		$item.=monthly_footer();
		setpageicon(substparms("%%BLOGURL%%%%THEMESPATH%%%%THEME%%images/apple-touch-icon.png"));			
		$item=preg_replace('/@@PAGEICON@@/',blogparm('PAGEICON'),$item);				
		gravaarquivonaraiz("archive-$anoproc-$mesproc.html",$item);
		if ($ano!=$anoarch) {
			$anoarch=$ano;
			setmonthlyindexdate($anoarch);
		} else setmonthlyindexdate("");
		setmonthlyindextitle("<a href='archive-$anoproc-$mesproc.html'>Posts publicados em $mesproc/$anoproc</a>");
		$archive.=monthly_arch();
		$item="";
	}
	$archive.=$blogparms['OLDARCHIVES'];
	$archive.=monthly_footer(0,0);
	gravaarquivonaraiz("archive.html",$archive);
	return $item;
}


?>