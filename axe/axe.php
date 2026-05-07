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

$axe_exepath = realpath(dirname($argv[0])).'/';
require $axe_exepath.'cli_dispatch.php';

$cli_defs = axe_cli_get_parameter_definitions();
$longparameters = $cli_defs['long'];
$shortparameters = $cli_defs['short'];

$options = getopt($shortparameters);
$options = axe_cli_fake_longopts($shortparameters, $longparameters, $options);
$arquivo = $argv[$argc - 1];
if (!(isset($options['delete']) || isset($options['X']))) {
	$arquivo = basename($arquivo);
}
$chamada = $argv[0];

$configfiledir = '';
if (isset($options['hereconf']) || isset($options['h'])) {
	$configfiledir = getcwd().'/';
}

require $axe_exepath.'axe_lib.php';
include blogparm('BLOGINCS').'indexes.php';
include blogparm('BLOGINCS').'axe_monthly.php';
loadpostvars();

$strict = (isset($options['strict']) || isset($options['r']));
$nofirstimage = (isset($options['nofirstimage']) || isset($options['1']));
$quiet = (isset($options['quiet']) || isset($options['q']));
$verbose = (isset($options['talkative']) || isset($options['t']));
$norebuild = (isset($options['norebuild']) || isset($options['n']));
$indexesonly = (isset($options['indexesonly']) || isset($options['i']));
$force = ($indexesonly || isset($options['force']) || isset($options['f']));
$noold = (isset($options['noold']) || isset($options['o']));

axe_cli_dispatch();
