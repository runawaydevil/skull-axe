<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MarkdownPrepareTest extends TestCase
{
	protected function setUp(): void
	{
		require_once dirname(__DIR__).'/axe/lib/markdown.php';
	}

	public function testLeavesBodyUnchangedWhenMarkdownDisabled(): void
	{
		global $blogparms;
		$blogparms = array('USE_MARKDOWN' => false);
		$raw = "# Título\n\nParágrafo com **negrito**.";
		$this->assertSame($raw, axe_prepare_post_body($raw));
	}

	public function testConvertsMarkdownWhenEnabledAndLibraryPresent(): void
	{
		if (!class_exists(\League\CommonMark\MarkdownConverter::class)) {
			$this->markTestSkipped('vendor/league/commonmark não instalado');
		}
		global $blogparms;
		$blogparms = array('USE_MARKDOWN' => true);
		$out = axe_prepare_post_body("# Olá\n\nTeste.");
		$this->assertStringContainsString('<h1>', $out);
		$this->assertStringContainsString('Olá', $out);
		$this->assertStringContainsString('<p>', $out);
	}
}
