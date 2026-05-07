<?php
/*
 * Configuração local — Laragon (Windows).
 * Ajuste BLOGURL / FEEDURL / PREVIEWSBASEURL se usar http://localhost/axe/ em vez de http://axe.test/
 */
global $blogparms;
global $axedir;

$blogparms["BLOGTITLE"] = 'Blog de teste (Axe)';
$blogparms["BLOGMOTTO"] = 'Teste no Laragon';
$blogparms["BLOGLOGO"] = 'http://axe.test/axethemes/panzer3/images/favicon.png';

$blogparms["BLOGURL"] = 'http://axe.test/';
$blogparms["BLOGTWITTER"] = '';
$blogparms["FEEDURL"] = 'http://axe.test/feed.xml';

$blogparms["BLOGOWNER"] = 'Teste';
$blogparms["BLOGOWNERURL"] = 'http://axe.test/';
$blogparms["BLOGOWNERTWITTER"] = '';

$blogparms["NUMPOSTSFEED"] = '10';
$blogparms["NUMPOSTSCOVER"] = '10';
$blogparms["NUMFEATSCOVER"] = '2';

$axedir = 'C:/laragon/www/axe/axe/';
$blogparms["THEMESDIR"] = 'C:/laragon/www/axe/axethemes/';
$blogparms["THEMESPATH"] = 'axethemes/';
$blogparms["THEME"] = 'panzer3/';
$blogparms["PLUGINSDIR"] = $axedir . 'plugins/';
$blogparms["POSTSDIR"] = 'C:/laragon/www/axe/';
$blogparms["POSTSURLPREFIX"] = '';
$blogparms["PREVIEWDIR"] = 'C:/laragon/www/axe/axepreview/';
$blogparms["PREVIEWSBASEURL"] = 'http://axe.test/axepreview/';

$blogparms["BLOGLOCALE"] = 'pt_BR';
$blogparms["EXIBIRPOPULARES"] = false;
// Markdown no corpo dos posts (requer `composer install` na raiz do projeto). false = desligado.
$blogparms["USE_MARKDOWN"] = false;

error_reporting(E_ALL ^ E_NOTICE ^ E_USER_NOTICE);
date_default_timezone_set('America/Sao_Paulo');
