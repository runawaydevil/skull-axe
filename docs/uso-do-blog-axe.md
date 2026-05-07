# Guia de uso do blog (Axe CMS)

Este site usa o **Axe**, um gerador de blog estático em PHP (Augusto Campos). O conteúdo público fica em HTML na raiz do projeto; o “motor” fica em `axe/`; o visual em `axethemes/`.

**Instalação em servidor:** [instalacao-debian-12.md](instalacao-debian-12.md) (Debian 12, Apache/Nginx manual). **Debian 13 + CloudPanel:** [instalacao-debian-13-cloudpanel.md](instalacao-debian-13-cloudpanel.md).

**Customização completa** (tema novo, placeholders, plugins, feed): [customizacao-completa-axe.md](customizacao-completa-axe.md).

## Estrutura importante

| Caminho | Função |
|--------|--------|
| `axe/axe_config.php` | Configuração do blog (URLs, pastas, título, tema). **É aqui que você ajusta ambiente (local x produção).** |
| `axe/axe_config_exemplo.php` | Modelo de configuração; copie/renomeie como referência. |
| `axe/axe.php` | Linha de comando principal (publicar, preview, rebuild). |
| `axe/staging/` | Onde você **coloca o rascunho em texto** antes de gerar o draft. |
| `axe/drafts/` | Rascunhos PHP gerados pelo Axe (pré-visualização ou publicação). |
| `axe/descriptors/posts/` | Descritores dos posts publicados (`YYYY/MM/slug.php`). |
| `axethemes/panzer3/` | Tema ativo (header, footer, CSS, templates de capa e post). |
| Raiz do site (`POSTSDIR` no config) | HTML publicado (`index.html`, `feed.xml`, `YYYY/MM/slug.html`, índices de tags etc.). |
| `axepreview/` | HTML de **preview** (não é o site final). |

No Laragon, o `axe_config.php` já aponta caminhos como `C:/laragon/www/axe/` e URLs como `http://axe.test/`. Se usar outro host ou pasta, atualize `BLOGURL`, `FEEDURL`, `PREVIEWSBASEURL` e os caminhos absolutos.

## Pré-requisito: rodar o Axe

No PowerShell ou terminal, a partir da pasta do executável:

```powershell
cd C:\laragon\www\axe\axe
php axe.php
```

Sem parâmetros, o script apenas lembra que é preciso passar um comando (`-d`, `-v`, `-P`, etc.).

### Preciso rodar comando toda vez que publicar?

**Sim.** O Axe é um gerador estático: nada vira post “oficial” na raiz do site sem passar pelo `php axe.php` com os parâmetros certos.

- **Fluxo em dois passos:** `staging` → `-d` (gera o draft) → `-P` (publica). Ou **um comando só:** `php axe.php -dP arquivo.txt`, que gera o draft, publica e já dispara o **rebuild** (capa, tags, feed etc.), salvo se você usar `-n` para pular o rebuild.
- **Só preview:** use `-d` ou `-dv` sem `-P`; nada disso altera o site público até você rodar `-P` (ou `-dP`).
- **Atualizar post existente:** `-U` ou `-dU` (com `POSTTIME`/`POSTNAME` no conteúdo), como descrito abaixo.

Não há painel web embutido: o “botão publicar” é o terminal (ou um script `.bat` / atalho que chame os mesmos comandos).

### Markdown

O corpo do arquivo em `staging` deve ser **HTML**. Se quiser escrever em Markdown, converta para HTML antes de colar no staging, ou estenda o PHP do Axe com um conversor (não vem pronto neste projeto).

## Fluxo típico: escrever um post novo

### 1. Criar o arquivo em `staging/`

Crie um arquivo de texto (`.txt` ou outro nome) em `axe/staging/` com este formato:

1. **Primeira linha:** título do post.  
2. **Segunda linha (opcional):** `tags:tag1,tag2` (sem espaços extras nas tags).  
3. **Corpo:** HTML do artigo (parágrafos, imagens, etc.).  
4. **Opcional:** linhas de metadados começando com `@@CHAVE:valor` (ver seção *Metadados* abaixo).  
5. **Opcional:** bloco `@@OLDCOMMENTS` para comentários antigos importados.

Exemplo mínimo:

```text
Meu primeiro post
tags:geral,axe

<p>Introdução em <strong>HTML</strong>.</p>
```

Há um exemplo arquivado em `axe/staging/old/` no repositório (conteúdo de teste).

### 2. Gerar o draft (`-d`)

```powershell
php axe.php -d nome-do-arquivo.txt
```

Isso:

- Lê `staging/nome-do-arquivo.txt`
- Gera um `.php` em `axe/drafts/` (nome normalizado a partir do título)
- Move o arquivo de entrada para `axe/staging/old/` (comportamento padrão)

Combinações úteis:

- `php axe.php -dv nome-do-arquivo.txt` — gera draft **e** preview HTML de uma vez.
- `php axe.php -dP nome-do-arquivo.txt` — draft **e** publicação (post) **e** rebuild.
- `php axe.php -dU nome-do-arquivo.txt` — draft **e** atualização de post existente **e** rebuild (só faz sentido se o draft for de **atualização**; ver abaixo).

### 3. Pré-visualizar (`-v`)

```powershell
php axe.php -v slug-normalizado.php
```

Lista os drafts disponíveis:

```powershell
php axe.php -v
```

O HTML sai em `axepreview/`; a URL aparece no terminal (ex.: `PREVIEWSBASEURL` + nome `.html`).

### 4. Publicar post novo (`-P`)

```powershell
php axe.php -P nome-do-draft.php
```

- Cria o descritor em `axe/descriptors/posts/AAAA/MM/...`
- Gera o `.html` na raiz do blog
- Atualiza catálogos, e por padrão executa **rebuild** (capa, tags, feed, etc.)

Para publicar **sem** rebuild imediato (casos raros):

```powershell
php axe.php -n -P nome-do-draft.php
```

(`-n` / `norebuild`)

## Atualizar um post já publicado

O Axe precisa saber **qual** post está sendo editado. No draft de atualização deve existir **`POSTTIME`** e **`POSTNAME`** (iguais ao post atual).

Forma prática:

1. Abra a página publicada no navegador ou o arquivo `.src.html` correspondente na raiz do site — o tema costuma incluir o **fonte editável** com linhas `@@POSTTIME:...`, `@@POSTNAME:...` e o corpo.
2. Copie esse conteúdo para um novo arquivo em `staging/`, altere o que precisar (título, tags, HTML).
3. Rode `php axe.php -d nome.txt` — quando há `POSTTIME` e `POSTNAME`, o draft gerado costuma ter prefixo `upd_` no nome.
4. Rode `php axe.php -U arquivo-draft.php` (ou `php axe.php -dU ...` em um passo só a partir do staging).

Se faltar `POSTTIME` ou `POSTNAME`, o Axe recusa a atualização. Se o `POSTNAME` não existir no catálogo, também erro.

## Remover um post (`-X`)

Informe o caminho **web relativo** do post, em geral como no site, terminando em `.html` ou `.php`:

```powershell
php axe.php -X 2025/05/meu-post.html
```

Isso remove o descritor, tira dos catálogos/tags e apaga a fonte editável; em seguida faz rebuild.

## Reconstruir índices e páginas (`-R`)

Depois de mudanças manuais ou para forçar regeneração:

```powershell
php axe.php -R
```

- `-R` com **force** (regenera também todos os singles): use as flags do Axe conforme sua versão (`-f` / `--force` no código).
- **Só índices** (sem regerar cada post): `-R` com `indexesonly` (`-i`), útil quando só mudou tema ou config de listagens.

Outros comandos (avançado):

- `-e` / feed — gera feed.
- `-L` — catálogos.
- `-m` — sitemap.
- `-M` — índices mensais.
- `-c` / `-C` — cron de posts agendados / prioridade (requer configuração de agendamento).

## Modo estrito (`-r` / `--strict`)

Com **strict**, a primeira linha **não** é tratada como título automático e a segunda não como tags. Você define tudo com `@@POSTTITLE:`, `@@POSTTAGS:`, etc. Útil para títulos que quebrariam o parser simples.

## Metadados úteis (`@@...`)

Além do corpo, você pode usar linhas como:

- `@@POSTICON:...` — ícone/imagem do post (ou use a primeira `<img>` do corpo, exceto se usar `-1` / `nofirstimage`).
- `@@CRON:...` — agendamento (fluxo de cron do Axe).
- `@@PRI:...` — prioridade para fila de publicação.
- Outros campos que o tema ou plugins esperem.

Consulte o código em `axe/axe_lib.php` (`gera_draft`) para o parsing exato.

## Imagens, vídeos e embeds

O corpo do post é **HTML**. O Axe **não** tem biblioteca de mídia nem upload automático: você escreve as tags e **coloca os ficheiros no servidor** (SFTP, git, etc.) quando quiser imagens ou vídeos locais.

### Imagens

- **No seu servidor:** guarde os ficheiros na árvore do site (por exemplo `images/`, ou pasta à escolha sob `POSTSDIR`) e use caminhos como `src="/images/foto.jpg"` ou URL absoluta `https://seudominio.tld/images/foto.jpg`. O Nginx/Apache serve-os como ficheiros estáticos, junto com o HTML gerado.
- **Externas:** qualquer URL em `<img src="https://...">` funciona (CDN, redes, stock).

### Vídeo e áudio

- **Plataformas (YouTube, Vimeo, etc.):** use o código de **incorporar** que o site fornece — em geral um `<iframe>...</iframe>`. Cole-o no HTML do corpo do post (no `staging`).
- **Ficheiros seus no servidor:** suba o `.mp4` (ou outro formato) para uma pasta pública e use `<video controls><source src="/videos/clip.mp4" type="video/mp4"></video>`.
- **Áudio:** `<audio controls>...</audio>` ou embed de terceiros (SoundCloud, Spotify, etc.) via o código que o serviço der (`iframe` ou script, conforme permitido pelo tema/página).

### Ícone do post (`%%POSTICON%%`) e primeira imagem

Por defeito, o motor pode usar a **primeira `<img>` do corpo** como miniatura/ícone nas listagens — um `<iframe>` de vídeo **não** conta como imagem. Para definir ícone manualmente use `@@POSTICON:` nos metadados (secção *Metadados úteis* abaixo), ou a flag `-1` / `nofirstimage` na CLI se não quiser inferência a partir da primeira imagem.

### Boas práticas no HTML do staging

- Aspas simples no texto de entrada são escapadas; o símbolo `$` também — ao colar iframes ou snippets do Word, revise o resultado no preview.
- Políticas **Content-Security-Policy** muito restritas no servidor podem bloquear alguns iframes; com configuração habitual (ex.: CloudPanel por defeito) os embeds comuns costumam funcionar.

## Configuração (`axe_config.php`)

Ajuste no mínimo:

- `BLOGTITLE`, `BLOGMOTTO`, `BLOGURL`, `FEEDURL`
- `axedir`, `THEMESDIR`, `POSTSDIR`, `PREVIEWDIR`
- `PREVIEWSBASEURL` (URL até a pasta de preview)
- `THEME` (ex.: `panzer3/`)

**Produção:** replique a lógica do exemplo com os caminhos do servidor Linux ou Windows do seu provedor. **Nunca** commite senhas; este CMS não exige banco — só arquivos.

## Segurança e `.htaccess`

O `.htaccess` na raiz redireciona acesso direto a algumas URLs (ex.: bloqueio de navegar em certas pastas). Mantenha `axe/` fora do que você expõe publicamente se o servidor permitir — no Laragon o projeto costuma servir tudo sob `www`; em produção, restrinja o que for possível.

## Resumo dos comandos

| Ação | Comando típico |
|------|----------------|
| Listar o que processar em staging | `php axe.php -d` |
| Staging → draft | `php axe.php -d arquivo.txt` |
| Draft → preview web | `php axe.php -v draft.php` |
| Draft → post novo | `php axe.php -P draft.php` |
| Draft → atualizar post | `php axe.php -U draft.php` |
| Rebuild geral | `php axe.php -R` |
| Apagar post | `php axe.php -X ano/mes/slug.html` |

## Onde pedir ajuda no código

- Ajuda de linha de comando: final de `axe/axe.php` (mensagem se faltar comando).
- Publicação / descritores: `axe/single.php`.
- Draft a partir do staging: `axe/axe_lib.php` (`gera_draft`).
- Variáveis de post (`%%POSTTITLE%%`, datas, URLs): `axe/axe_lib.php` (`loadpostvars` e afins).

---

*Documentação gerada para o projeto em `C:\laragon\www\axe`, alinhada ao Axe 0.98a.4 referenciado em `axe_lib.php`.*
