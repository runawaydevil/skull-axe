#!/usr/bin/php
<?php
/*
	Axe: gerenciador de conteúdo estático
		
	sintaxe: axe.php parâmetros [arquivo-de-entrada]

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


function fake_longopts($shortp,$longp,$options,$cmd="") {
	// descobri de maneira inesperada que
	// infelizmente nem todo provedor de hospedagem instala 
	// um PHP com suporte nativo a longopts no getopt() 
	global $argv;
	global $argc;
	if ($cmd=="list") {
		for ($i=0;$i<strlen($shortp);$i++) {
			echo("-$shortp[$i] ou --$longp[$i]\n");
		}
	}
	else {
		for ($j=1;$j<$argc;$j++) {
			//TO DO: substituir este loop por uma busca simples no array
			for ($i=0;$i<strlen($shortp);$i++) {
				if ($argv[$j]=="--".$longp[$i]) {
					$options[$shortp[$i]]=false;
				}	
			}
		}	
	} 
	return $options;
}



// carrega os parametros da linha de comando, se houver
// a lista long e a lista short precisam estar na mesma ordem
$longparameters = array("draft", "preview", "post", "update", "rebuild", "monthly", "delete", "catalogs", "cron", "cronpriority", "getnotify", "feed",
						"map", "force", "quiet", "norebuild","noold","simul","talkative","strict","nofirstimage","indexesonly","hereconf");
$shortparameters = "dvPURMXLcCgemfqnostr1ih"; 

$options = getopt($shortparameters); /*, $longparameters);  ---> ver nota na função fake_longopts */
$options=fake_longopts($shortparameters,$longparameters,$options);
$arquivo=$argv[$argc-1];
if (!(isset($options['delete'])||isset($options['X']))) $arquivo=basename($arquivo);
$chamada=$argv[0];

$configfiledir="";
if (isset($options['hereconf']) || isset($options['h'])) $configfiledir=getcwd()."/";

global $argv;
$axe_exepath=realpath(dirname($argv[0]))."/";
require $axe_exepath.'axe_lib.php';
include blogparm('BLOGINCS').'indexes.php';
include blogparm('BLOGINCS').'axe_monthly.php';
loadpostvars();


$strict=(isset($options['strict']) || isset($options['r'])); // exige @@, não interpreta título e tags nas primeiras linhas
$nofirstimage=(isset($options['nofirstimage']) || isset($options['1'])); // não atribui a primeira imagem ao %%POSTICON%%, que precisará ser definido explicitamente
$quiet=(isset($options['quiet']) || isset($options['q'])); // desativa a saída da função decho()
$verbose=(isset($options['talkative']) || isset($options['t']));
$norebuild=(isset($options['norebuild']) || isset($options['n']));
$indexesonly=(isset($options['indexesonly']) || isset($options['i'])); // super rebuild - regera tudo menos os posts. Assume $force 
$force=($indexesonly || isset($options['force']) || isset($options['f'])); 
$noold=(isset($options['noold']) || isset($options['o'])); // nao move para o staging/old após gerar draft

// define a ação a ser executada, em ordem de precedência 

// draft
if (isset($options['draft']) || isset($options['d'])) {					
	if ($argc==2) { // não tem outros parâmetros
  		lista_arquivos_processaveis("Arquivos disponíveis na pasta staging:",blogparm('STAGINGDIR'),"$chamada -d");
  		exit;
	} else if (isset($options['v'])) {
		// -dv = draft + preview
		$arqsaiu=gera_draft($arquivo,$quiet);
		$nomeout=gera_single($arqsaiu,true,true);
		if (!$quiet) {
			dmsg("Acessível pela web em:");
			$nomepreview=preg_replace('/\.php$/',".html",basename($nomeout));
			echo(blogparm('PREVIEWSBASEURL').$nomepreview."\n");
			dmsg("Possíveis próximos passos:");
			echo("$chamada -P ".basename($arquivo)."\n");
			echo("$chamada -U ".basename($arquivo)."\n");	
		}		
	} else if (isset($options['P'])) {
		// -dP = draft + post
		$arqsaiu=gera_draft($arquivo,$quiet);
		$nomeout=cria_descriptor($arqsaiu,true,true);
		dmsg("Processei: ".blogparm('POSTSDIR').$nomeout);
		sched_notify_post();		
		if (!$norebuild) rebuild();				
	} else if (isset($options['U'])) {
		// -dU = draft + update
		$arqsaiu=gera_draft($arquivo,$quiet);
		$nomeout=atualiza_descriptor($arqsaiu,true,true);
		$nomeout=gera_single($nomeout);
		dmsg("Processei: ".blogparm('POSTSDIR').$nomeout);
		if (!$norebuild) rebuild();
	} else {
		$arqsaiu=gera_draft($arquivo,$quiet);
		if (!$quiet) {
			dmsg("Possíveis próximos passos:");
			echo("$chamada -v $arqsaiu		 #preview\n");
			echo("$chamada -P $arqsaiu		 #postar\n");
			echo("$chamada -U $arqsaiu		 #update\n");
		}		
	}	
}
// preview
else if (isset($options['preview']) || isset($options['v'])) {		
	if ($argc==2) { // não tem outros parâmetros
  		lista_arquivos_processaveis("Arquivos disponíveis na pasta draft:",blogparm('DRAFTSDIR'),"$chamada -v");
  		exit;
	} 
	$nomeout=gera_single($arquivo,true,true);
	if (!$quiet) {
		dmsg("Processei: ".blogparm('PREVIEWDIR').$arquivo);
		dmsg("Acessível pela web em:");
		$nomepreview=preg_replace('/\.php$/',".html",basename($nomeout));
		echo(blogparm('PREVIEWSBASEURL').$nomepreview."\n");
		dmsg("Possíveis próximos passos:");
		echo("$chamada -P ".basename($arquivo)."\n");
		echo("$chamada -U ".basename($arquivo)."\n");	
	}
} 

// post
else if (isset($options['post']) || isset($options['P'])) {									
	if ($argc==2) {
	  lista_arquivos_processaveis("Arquivos disponíveis na pasta de drafts:",blogparm('DRAFTSDIR'),"$chamada -P");
	  exit;
	}
	$nomeout=cria_descriptor($arquivo,true,true);
	dmsg("Processei: ".blogparm('POSTSDIR').$nomeout);
	sched_notify_post();
	if (!$norebuild) rebuild();
} 

// update
else if (isset($options['update']) || isset($options['U'])) {		
	if ($argc==2) {
	  lista_arquivos_processaveis("Arquivos disponíveis na pasta de drafts:",blogparm('DRAFTSDIR'),"$chamada -U");
	  exit;
	}
	$nomeout=atualiza_descriptor($arquivo,true,true);
	$nomeout=gera_single($nomeout);
	dmsg("Processei: ".blogparm('POSTSDIR').$nomeout);
	if (!$norebuild) rebuild();
} 

// rebuild
else if (isset($options['rebuild']) || isset($options['R'])) {		
	if ($indexesonly) {
		dmsg("Regerando todos os índices, tags, archives e sitemaps - e nenhum post.");
		echo rebuild($force);
	} else {
		if ($force) {
			dmsg("Forçando a reconstrução de todos os arquivos de posts individuais");
			echo rebuild($force);
		} else {
			rebuild();
		}	
	}	
}

// delete
else if (isset($options['delete'])  || isset($options['X'])) {		
		apaga_single($arquivo);
		rebuild();
}	

// cron para postar agendados e notificacoes
else if (isset($options['cron'])  || isset($options['c'])) {		
	$pendente=verifica_agendamentos();
	if (strlen($pendente)>5) {
		dmsg("Vou agendar para a crontab: $pendente");
		$nomeout=cria_descriptor($pendente,true,true);
		dmsg("Processei: ".blogparm('POSTSDIR').$nomeout);
		sched_notify_post();		
		if (!$norebuild) rebuild();
	}
}	

// cron para postar classificados por prioridade
else if (isset($options['cronpriority'])  || isset($options['C'])) {		
	if (isset($options['simul'])  || isset($options['s'])) {
		simula_prioridades(8,12);
		exit;
	}
	$pendente=verifica_prioritarios();
	if (strlen($pendente)>5) {
		dmsg("Vou agendar via prioridades: $pendente");
		$nomeout=cria_descriptor($pendente,true,true);
		sched_notify_post();		
		if (!$norebuild) rebuild();
	}
}	

// gera indices de posts por mês
else if (isset($options['monthly'])  || isset($options['M'])) {
        monthly_index();
}

// interface para notificador externo
else if (isset($options['getnotify'])  || isset($options['g'])) {
        verifica_notificacoes();
}

// feed
else if (isset($options['feed'])  || isset($options['e'])) {
        gera_feed();
}
// catalogs
else if (isset($options['catalogs'])  || isset($options['L'])) {
        rebuild_catalogs();
}
// sitemap
else if (isset($options['map'])  || isset($options['m'])) {
        sitemap();
}
                
                
else {
	echo("Usuário não incluiu comando: -d (draft), -v (preview), -P (post), -U (update), -R (rebuild), -X (delete). \nEncerrando sem ação.\n");
	die;
}

if (!$quiet) echo "\n";



