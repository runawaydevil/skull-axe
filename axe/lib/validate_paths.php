<?php
/*
 * Validação de caminhos e URLs da configuração (extraído de axe_lib.php).
 */

function valida_dir(string $dir, string $nome): string {
	if (0 === strlen($dir)) {
		axe_error("Configuração: $nome indefinido ou vazio");
	}
	if (substr($dir, -1) != "/") {
		$dir .= "/";
	}
	if (!is_dir($dir)) {
		axe_error("Configuração: diretório $dir inexistente");
	}
	if (!is_writable($dir)) {
		axe_error("Configuração: diretório $dir sem permissão de gravação");
	}
	return ($dir);
}

function valida_path(string $dir, string $nome): string {
	if (0 === strlen($dir)) {
		axe_error("Configuração: $nome indefinido ou vazio");
	}
	if (substr($dir, -1) != "/") {
		$dir .= "/";
	}
	return ($dir);
}

function valida_url(string $url, string $nome = ""): string {
	if (0 === strlen($url)) {
		axe_error("Configuração: $nome indefinido ou vazio");
	}
	if (substr($url, -1) != "/") {
		$url .= "/";
	}
	return ($url);
}
