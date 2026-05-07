<?php
/*
 * CLI do Axe: definição de flags e despacho de comandos.
 * Mantém as mesmas opções curtas/longas que o getopt histórico.
 */

/**
 * @return array{long: string[], short: string}
 */
function axe_cli_get_parameter_definitions() {
	return array(
		'long' => array(
			'draft', 'preview', 'post', 'update', 'rebuild', 'monthly', 'delete', 'catalogs', 'cron', 'cronpriority', 'getnotify', 'feed',
			'map', 'force', 'quiet', 'norebuild', 'noold', 'simul', 'talkative', 'strict', 'nofirstimage', 'indexesonly', 'hereconf',
		),
		'short' => 'dvPURMXLcCgemfqnostr1ih',
	);
}

/**
 * Compatibilidade com PHP sem longopts em getopt(): reconhece --nome e mapeia para a opção curta.
 *
 * @param string $shortp
 * @param string[] $longp
 * @param array<string, mixed> $options
 * @param string $cmd
 * @return array<string, mixed>
 */
function axe_cli_fake_longopts($shortp, $longp, $options, $cmd = '') {
	global $argv;
	global $argc;
	if ($cmd === 'list') {
		for ($i = 0; $i < strlen($shortp); $i++) {
			echo("-$shortp[$i] ou --$longp[$i]\n");
		}
	} else {
		for ($j = 1; $j < $argc; $j++) {
			for ($i = 0; $i < strlen($shortp); $i++) {
				if ($argv[$j] === '--'.$longp[$i]) {
					$options[$shortp[$i]] = false;
				}
			}
		}
	}
	return $options;
}

/**
 * Despacho principal após axe_lib carregado, loadpostvars() e variáveis globais de comportamento definidas.
 */
function axe_cli_dispatch() {
	global $options;
	global $argc;
	global $argv;
	global $arquivo;
	global $chamada;
	global $quiet;
	global $norebuild;
	global $indexesonly;
	global $force;

	if (isset($options['draft']) || isset($options['d'])) {
		if ($argc == 2) {
			lista_arquivos_processaveis('Arquivos disponíveis na pasta staging:', blogparm('STAGINGDIR'), "$chamada -d");
			exit;
		} elseif (isset($options['v'])) {
			$arqsaiu = gera_draft($arquivo, $quiet);
			$nomeout = gera_single($arqsaiu, true, true);
			if (!$quiet) {
				dmsg('Acessível pela web em:');
				$nomepreview = preg_replace('/\.php$/', '.html', basename($nomeout));
				echo(blogparm('PREVIEWSBASEURL').$nomepreview."\n");
				dmsg('Possíveis próximos passos:');
				echo("$chamada -P ".basename($arquivo)."\n");
				echo("$chamada -U ".basename($arquivo)."\n");
			}
		} elseif (isset($options['P'])) {
			$arqsaiu = gera_draft($arquivo, $quiet);
			$nomeout = cria_descriptor($arqsaiu);
			dmsg('Processei: '.blogparm('POSTSDIR').$nomeout);
			sched_notify_post();
			if (!$norebuild) {
				rebuild();
			}
		} elseif (isset($options['U'])) {
			$arqsaiu = gera_draft($arquivo, $quiet);
			$nomeout = atualiza_descriptor($arqsaiu);
			$nomeout = gera_single($nomeout);
			dmsg('Processei: '.blogparm('POSTSDIR').$nomeout);
			if (!$norebuild) {
				rebuild();
			}
		} else {
			$arqsaiu = gera_draft($arquivo, $quiet);
			if (!$quiet) {
				dmsg('Possíveis próximos passos:');
				echo("$chamada -v $arqsaiu		 #preview\n");
				echo("$chamada -P $arqsaiu		 #postar\n");
				echo("$chamada -U $arqsaiu		 #update\n");
			}
		}
	} elseif (isset($options['preview']) || isset($options['v'])) {
		if ($argc == 2) {
			lista_arquivos_processaveis('Arquivos disponíveis na pasta draft:', blogparm('DRAFTSDIR'), "$chamada -v");
			exit;
		}
		$nomeout = gera_single($arquivo, true, true);
		if (!$quiet) {
			dmsg('Processei: '.blogparm('PREVIEWDIR').$arquivo);
			dmsg('Acessível pela web em:');
			$nomepreview = preg_replace('/\.php$/', '.html', basename($nomeout));
			echo(blogparm('PREVIEWSBASEURL').$nomepreview."\n");
			dmsg('Possíveis próximos passos:');
			echo("$chamada -P ".basename($arquivo)."\n");
			echo("$chamada -U ".basename($arquivo)."\n");
		}
	} elseif (isset($options['post']) || isset($options['P'])) {
		if ($argc == 2) {
			lista_arquivos_processaveis('Arquivos disponíveis na pasta de drafts:', blogparm('DRAFTSDIR'), "$chamada -P");
			exit;
		}
		$nomeout = cria_descriptor($arquivo);
		dmsg('Processei: '.blogparm('POSTSDIR').$nomeout);
		sched_notify_post();
		if (!$norebuild) {
			rebuild();
		}
	} elseif (isset($options['update']) || isset($options['U'])) {
		if ($argc == 2) {
			lista_arquivos_processaveis('Arquivos disponíveis na pasta de drafts:', blogparm('DRAFTSDIR'), "$chamada -U");
			exit;
		}
		$nomeout = atualiza_descriptor($arquivo);
		$nomeout = gera_single($nomeout);
		dmsg('Processei: '.blogparm('POSTSDIR').$nomeout);
		if (!$norebuild) {
			rebuild();
		}
	} elseif (isset($options['rebuild']) || isset($options['R'])) {
		if ($indexesonly) {
			dmsg('Regerando todos os índices, tags, archives e sitemaps - e nenhum post.');
			echo rebuild($force);
		} else {
			if ($force) {
				dmsg('Forçando a reconstrução de todos os arquivos de posts individuais');
				echo rebuild($force);
			} else {
				rebuild();
			}
		}
	} elseif (isset($options['delete']) || isset($options['X'])) {
		apaga_single($arquivo);
		rebuild();
	} elseif (isset($options['cron']) || isset($options['c'])) {
		$pendente = verifica_agendamentos();
		if (strlen($pendente) > 5) {
			dmsg("Vou agendar para a crontab: $pendente");
				$nomeout = cria_descriptor($pendente);
			dmsg('Processei: '.blogparm('POSTSDIR').$nomeout);
			sched_notify_post();
			if (!$norebuild) {
				rebuild();
			}
		}
	} elseif (isset($options['cronpriority']) || isset($options['C'])) {
		if (isset($options['simul']) || isset($options['s'])) {
			simula_prioridades(8, 12);
			exit;
		}
		$pendente = verifica_prioritarios();
		if (strlen($pendente) > 5) {
			dmsg("Vou agendar via prioridades: $pendente");
			$nomeout = cria_descriptor($pendente);
			sched_notify_post();
			if (!$norebuild) {
				rebuild();
			}
		}
	} elseif (isset($options['monthly']) || isset($options['M'])) {
		monthly_index();
	} elseif (isset($options['getnotify']) || isset($options['g'])) {
		verifica_notificacoes();
	} elseif (isset($options['feed']) || isset($options['e'])) {
		gera_feed();
	} elseif (isset($options['catalogs']) || isset($options['L'])) {
		rebuild_catalogs();
	} elseif (isset($options['map']) || isset($options['m'])) {
		sitemap();
	} else {
		echo("Usuário não incluiu comando: -d (draft), -v (preview), -P (post), -U (update), -R (rebuild), -X (delete). \nEncerrando sem ação.\n");
		die;
	}

	if (!$quiet) {
		echo "\n";
	}
}
