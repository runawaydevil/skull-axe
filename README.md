# Blog estático com Axe CMS

O projeto original do **Axe** é de **[Augusto Campos](http://augustocampos.net/)** — CMS estático em PHP (sem base de dados).

**Este repositório** é um *fork* mantido por **Pablo Murad** para o seu blog pessoal. Inclui, entre outras coisas: documentação em `docs/`, **Markdown opcional** (`USE_MARKDOWN` + Composer), **cache bust** do CSS do tema (`%%CSSVERSION%%`), **CLI** refatorada (`axe/cli_dispatch.php`), módulos em **`axe/lib/`**, **HTML5 e landmarks** no tema panzer3, testes **PHPUnit** e **GitHub Actions** (lint + testes). O motor base segue o Axe: HTML estático na raiz; código em `axe/`; visual em `axethemes/`.

## Requisitos

- **PHP** ≥ 7.4 com CLI (`php`) para publicar e regenerar páginas.
- Servidor web opcional para desenvolvimento local (ex.: [Laragon](https://laragon.org/), Apache/Nginx em Linux).
- Opcional: [Composer](https://getcomposer.org/) na raiz do projeto (`composer install`) para Markdown (`USE_MARKDOWN`) e para correr os testes em CI.

## Arranque rápido

1. Copie `axe/axe_config_exemplo.php` para `axe/axe_config.php` e ajuste caminhos e URLs ao seu ambiente.
2. (Opcional) Na raiz do repositório: `composer install` — necessário se usar `$blogparms["USE_MARKDOWN"] = true` em `axe_config.php`.
3. Escreva um artigo em texto/HTML em `axe/staging/` (título na primeira linha; ver documentação).
4. Na pasta `axe/` execute:

   ```bash
   php axe.php -dP nome-do-ficheiro.txt
   ```

   Isto gera o draft, publica e faz rebuild da capa, feed e índices.

Comandos completos, atualização de posts e flags: **[docs/uso-do-blog-axe.md](docs/uso-do-blog-axe.md)**.

## Checklist de deploy e segurança

Antes de produção: permissões nos descritores PHP, HTTPS no `axe_config.php`, rebuild após mudar URLs, Nginx vs `.htaccess`. Lista completa: **[docs/seguranca-e-deploy.md](docs/seguranca-e-deploy.md)**.

## Estrutura principal

| Pasta / ficheiro | Função |
|------------------|--------|
| `axe/` | Motor (`axe.php`, `cli_dispatch.php`, `axe_lib.php`, `axe_config.php`, `lib/`, `staging/`, `drafts/`, descritores, plugins). |
| `axe/lib/` | Módulos incluídos pelo motor (validação de paths, Markdown opcional). |
| `axethemes/panzer3/` | Tema por defeito (templates HTML5, `css/style.css`, imagens). |
| Raiz do projeto | HTML servido ao visitante (`index.html`, `feed.xml`, posts por ano/mês). |
| `composer.json`, `composer.lock` | Dependências PHP (CommonMark, PHPUnit em dev). |
| `vendor/` | Gerado por `composer install` (não versionado; ver `.gitignore`). |
| `tests/`, `phpunit.xml` | Testes automatizados. |
| `.github/workflows/ci.yml` | CI: `php -l` + PHPUnit. |
| `axepreview/` | Pré-visualizações HTML locais. |
| `docs/` | Documentação deste repositório. |

## Documentação

| Documento | Conteúdo |
|-----------|----------|
| [docs/uso-do-blog-axe.md](docs/uso-do-blog-axe.md) | Fluxo de publicação, comandos, multimédia e embeds. |
| [docs/customizacao-completa-axe.md](docs/customizacao-completa-axe.md) | Tema próprio, placeholders, plugins, feed. |
| [docs/instalacao-debian-12.md](docs/instalacao-debian-12.md) | Debian 12, Apache/Nginx. |
| [docs/instalacao-debian-13-cloudpanel.md](docs/instalacao-debian-13-cloudpanel.md) | Debian 13 e CloudPanel. |
| [docs/seguranca-e-deploy.md](docs/seguranca-e-deploy.md) | Riscos (descritores PHP, `.src.html`), Markdown, `NOTIFYCMD`, Composer/CI, checklist. |

## Licença do motor Axe

O código do Axe (ficheiros em `axe/` salvo configuração local) segue a licença indicada nos cabeçalhos dos ficheiros — originalmente **Apache License 2.0** (Augusto Campos). Consulte os comentários em `axe/axe.php` e `axe/axe_lib.php`.

## Créditos

- **Axe CMS (projeto original)** — Augusto Campos · [augustocampos.net](http://augustocampos.net/).
- **Fork e uso** — Pablo Murad utiliza este repositório para o seu blog; inclui modificações próprias e documentação em português neste ramo.
