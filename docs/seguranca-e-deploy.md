# Segurança e deploy (fork Axe)

Documento complementar ao [uso-do-blog-axe.md](uso-do-blog-axe.md) e aos guias de instalação. Resume **riscos** inerentes ao modelo do Axe e um **checklist** antes de colocar o site em produção.

## Modelo de confiança

O Axe foi desenhado para **um único dono** que publica por SSH/CLI. Quem pode **escrever ficheiros** nas pastas do motor e dos descritores tem, na prática, o mesmo poder que quem pode **executar PHP** nesse servidor.

## Descritores como código PHP

Cada artigo publicado gera um ficheiro `.php` em `axe/descriptors/posts/…` que é carregado com `include` durante o build e na geração do HTML.

- **Risco:** qualquer conteúdo malicioso **dentro** desse PHP (se alguém alterar o ficheiro à mão ou comprometer o sistema de ficheiros) executa no próximo `php axe.php` ou na próxima regeneração.
- **Mitigação:** apenas o teu utilizador (ou CI com chave restrita) deve ter permissão de escrita em `axe/descriptors/` e `axe/drafts/`. Não partilhes SFTP de escrita com terceiros não confiáveis. Em equipa, usa revisão de PR só sobre `staging` e gera descritores em pipeline controlado.

## Ficheiros `.src.html` (fonte editável)

O tema pode gerar um `.src.html` por post com o texto de reedição (metadados + corpo).

- **Risco:** se esse URL for **indexável** ou ligado publicamente, expões estrutura interna e metadados.
- **Risco adicional:** o ficheiro mistura `<textarea>`, título em HTML e corpo livre; conteúdo muito invulgar no título ou no corpo pode **quebrar o contexto HTML** ao abrir o `.src.html` no browser. Trata estes ficheiros como **ferramenta interna** do publicador, não como página pública.
- **Mitigação:** o modelo inclui `noindex` em alguns casos — confirma no HTML gerado. Em Nginx/CloudPanel, podes bloquear padrão `*.src.html` com `deny all` ou apenas não linkar esses URLs. Preferência: **não** depender de `.src.html` em produção se não for necessário.

## `.htaccess` vs Nginx

A raiz do projeto pode incluir [`.htaccess`](../.htaccess) com rewrites para Apache.

- **Nginx e CloudPanel** não aplicam `.htaccess`. Regras equivalentes (bloquear pastas, ficheiros sensíveis) têm de ir para a configuração do servidor ou para o painel.
- Revisa se `/axe/` ou `/axe/staging` devem ser **inacessíveis** pela web quando o document root engloba tudo.

## Permissões (Linux / VPS)

- Utilizador que corre `php axe.php` precisa de **escrita** em: `POSTSDIR`, `PREVIEWDIR`, `axe/staging`, `axe/drafts`, `axe/descriptors`, etc. (validado em `axe_init`).
- O servidor web (`www-data` ou utilizador do site) só precisa de **leitura** nos HTML e estáticos servidos ao público, salvo se o mesmo utilizador publica por CLI — nesse caso alinha grupo e `umask`.

## Markdown opcional (`USE_MARKDOWN`)

- Em `axe_config.php`, `$blogparms["USE_MARKDOWN"] = true` faz com que o corpo dos posts (`POSTBODY`) seja interpretado como **Markdown** antes do pipeline habitual (`corrigehtml`, etc.).
- É necessário **`composer install` na raiz do repositório** para instalar `league/commonmark` e gerar `vendor/autoload.php`. O motor carrega o autoload automaticamente se o ficheiro existir.
- Com a flag ativa e sem `vendor/`, o Axe emite um aviso PHP e trata o corpo como texto simples (sem converter).
- A conversão usa **escape de HTML** no Markdown e **links inseguros desligados** (`javascript:`, etc.) — ver `axe/lib/markdown.php`. Isto **não** substitui o modelo de confiança: o HTML final servido aos visitantes continua a poder incluir markup arbitrário se escreveres HTML directamente (flag Markdown desligada) ou após conversão seguida de `corrigehtml`.

## Notificações externas (`NOTIFYCMD`, avançado)

O código legado inclui `do_notify_post()` em `axe_lib.php`, que pode executar **`system()`** com um comando definido em configuração (`NOTIFYCMD`) e texto do post.

- **Risco:** uso incorrecto ou texto com caracteres que quebram o *shell* pode levar a **injeção de comando**. Só deves activar este caminho se souberes exactamente o que estás a fazer.
- **Mitigação por defeito:** na configuração típica do Axe isto **não** está em uso; mantém assim salvo implementares uma variante segura (argumentos com `escapeshellarg`, comando fixo, sem concatenação frágil).

## Catálogos e `include`

Entradas em `descriptors/posts/catalog.txt` apontam para ficheiros sob `axe/descriptors/posts/`. Se alguém com acesso de escrita **adulterar** o catálogo com caminhos inesperados, o comportamento do `include` pode degradar-se. Mantém **integridade dos ficheiros de catálogo** como parte da mesma política de permissões dos descritores.

## Dependências e CI

- Com [`composer.json`](../composer.json) na raiz: corre **`composer audit`** periodicamente em desenvolvimento.
- O workflow [`.github/workflows/ci.yml`](../.github/workflows/ci.yml) valida sintaxe PHP (`php -l`) e PHPUnit; não substitui revisão humana nem auditoria de servidor.

## Checklist de deploy

- [ ] `axe/axe_config.php` com caminhos absolutos corretos e URLs finais (**https** após certificado).
- [ ] Permissões: só contas confiáveis com escrita no motor e descritores.
- [ ] SSL ativo; `BLOGURL` / `FEEDURL` / `PREVIEWSBASEURL` coerentes com HTTPS.
- [ ] Após mudar URL base: `php axe.php -R` (ou `-Rf` conforme necessidade) para regenerar links.
- [ ] Regras do servidor alinhadas com o `.htaccess` (Apache) ou equivalentes (Nginx).
- [ ] Backup: `axe/descriptors/`, `axe/staging` (se tiveres rascunhos), e raiz dos HTML.
- [ ] **Markdown opcional:** se usares `USE_MARKDOWN`, corre `composer install` na raiz do repositório para instalar `league/commonmark`.

## Dependências Composer (opcional)

Se o projeto tiver [`composer.json`](../composer.json), em produção **só** é necessário instalar dependências na máquina onde corres **`php axe.php`** se activares **Markdown** (`USE_MARKDOWN`). O servidor web que serve apenas `.html` estático **não** precisa do `vendor/` para os visitantes.

Para desenvolvimento: `composer install` na raiz também instala PHPUnit; a pasta `vendor/` está no [`.gitignore`](../.gitignore) — versiona o `composer.lock` para builds reprodutíveis.

---

*Fork Pablo Murad — documentação de segurança e operações (alinhada a `axe/lib/`, CLI em `cli_dispatch.php`, tema HTML5).*
