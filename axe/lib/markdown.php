<?php
/*
 * Markdown opcional (league/commonmark via Composer na raiz do projeto).
 */

/**
 * USE_MARKDOWN em axe_config: true, 1, '1' ou 'true' ativa a conversão.
 */
function axe_use_markdown() {
	global $blogparms;
	if (!isset($blogparms['USE_MARKDOWN'])) {
		return false;
	}
	$v = $blogparms['USE_MARKDOWN'];
	if ($v === true || $v === 1) {
		return true;
	}
	if (is_string($v)) {
		$v = strtolower(trim($v));
		return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
	}
	return false;
}

/**
 * Converte o corpo do post de Markdown para HTML se USE_MARKDOWN estiver ativo e a biblioteca existir.
 *
 * @param string $raw
 * @return string
 */
function axe_prepare_post_body($raw) {
	if (!axe_use_markdown()) {
		return $raw;
	}
	if (!is_string($raw) || $raw === '') {
		return $raw;
	}
	if (!class_exists(\League\CommonMark\MarkdownConverter::class)) {
		trigger_error('Axe: USE_MARKDOWN está ativo mas league/commonmark não foi encontrado. Execute composer install na raiz do projeto.', E_USER_WARNING);
		return $raw;
	}
	$config = array(
		'html_input' => 'escape',
		'allow_unsafe_links' => false,
	);
	$environment = new \League\CommonMark\Environment\Environment($config);
	$environment->addExtension(new \League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension());
	$converter = new \League\CommonMark\MarkdownConverter($environment);
	return $converter->convert($raw)->getContent();
}
