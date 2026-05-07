# Debian 13 + CloudPanel — Axe CMS

Guia para hospedar o **Axe** numa VPS **Debian 13** com **[CloudPanel](https://www.cloudpanel.io/)** já instalado. O painel passa a gerir Nginx, PHP, SSL e usuários do site; você só precisa encaixar os ficheiros do blog e apontar o `axe_config.php` para os caminhos corretos.

> **Relação com outros guias:** a lógica do Axe (staging, `php axe.php`, temas) é a mesma que em [uso-do-blog-axe.md](uso-do-blog-axe.md). Para um servidor **sem** CloudPanel, use [instalacao-debian-12.md](instalacao-debian-12.md) — os conceitos de pastas e `POSTSDIR` repetem-se aqui.

## Premissas

- VPS com **Debian 13** e **CloudPanel v2** (instalação oficial já feita pelo instalador do CloudPanel).
- CloudPanel usa **Nginx** e associa cada site a um **utilizador Linux** e à pasta `htdocs`. **Não** substitua manualmente os virtual hosts que o painel gera, salvo saber exatamente o que está a fazer — prefira **Domínios / Definições** no painel.

## O que o CloudPanel já traz

- Nginx, PHP-FPM (várias versões), Let’s Encrypt no painel.
- Caminho típico do site (document root público):

```text
/home/<utilizador-do-site>/htdocs/<dominio>/public/
```

O `<utilizador-do-site>` é o utilizador que criou quando adicionou o site no CloudPanel (por exemplo `cloudpanel` ou um utilizador dedicado). Confirme em **Sites → o seu site → Definições do domínio → Root Directory** — deve terminar em `/public` para servir a pasta pública.

## 1. Criar o site no CloudPanel

1. **Sites → Adicionar site** → escolha **PHP** ou **Static HTML** (para o Axe, ambos funcionam: o público são ficheiros `.html`; o PHP só é necessário na **CLI** para `axe.php`).
2. Associe o **domínio** (e `www` se quiser).
3. Anote o **utilizador do site** e o caminho completo até **`public`** — vai usar isto no `axe_config.php`.

Ative **SSL** no painel (Let’s Encrypt) e force **HTTPS**. Depois configure no Axe URLs com `https://`.

## 2. Colocar o Axe dentro do document root

O ideal é que **tudo** o que o Axe precisa viva **abaixo** da mesma raiz que define como `POSTSDIR`: a raiz `public` do site no CloudPanel.

Estrutura recomendada (espelha o projeto local):

```text
/home/<user>/htdocs/<dominio>/public/
├── axe/                 # motor (axe.php, axe_config.php, staging, drafts, descriptors…)
├── axethemes/           # temas (ex.: panzer3/)
├── axepreview/          # previews HTML (opcional)
├── index.html           # gerados pelo Axe
├── feed.xml
├── .htaccess            # se copiou da raiz do projeto; Nginx não usa .htaccess — ver nota abaixo
└── …
```

Envio de ficheiros:

- **SFTP/SSH** como utilizador do site (CloudPanel permite criar utilizadores SFTP por site), ou
- `git clone` / `rsync` para `~/htdocs/<dominio>/public/` com esse utilizador.

**Permissões:** o utilizador que corre `php axe.php` (normalmente o mesmo do site, por SSH) deve ser **dono** dos ficheiros ou pertencer ao grupo com escrita em `axe/`, `axepreview/` e na raiz `public`.

```bash
# Exemplo (ajuste utilizador e caminho)
sudo chown -R siteuser:siteuser /home/siteuser/htdocs/exemplo.pt/public
```

## 3. PHP CLI no Debian 13 com CloudPanel

O instalador do CloudPanel costuma registar várias versões de PHP. Para publicar na shell:

```bash
which php
php -v
```

Se `php` não existir no PATH, experimente a versão instalada pelo painel, por exemplo:

```bash
/usr/bin/php8.3 -v
# ou
/opt/cloudpanel/php/*/bin/php -v
```

Use **o mesmo binário** nos seus scripts ou cron:

```bash
cd /home/siteuser/htdocs/exemplo.pt/public/axe
/usr/bin/php8.3 axe.php -R
```

Instalar apenas o CLI do Debian **à parte** também é possível (`sudo apt install php-cli`), mas pode divergir da versão do painel — para consistência, prefira o PHP que o CloudPanel já configurou.

## 4. Configurar `axe_config.php`

Copie o exemplo e edite com caminhos **absolutos Linux** e URLs **HTTPS**:

```bash
cp axe/axe_config_exemplo.php axe/axe_config.php
nano axe/axe_config.php
```

Exemplo de valores (substitua utilizador, domínio e PHP real):

| Variável | Exemplo |
|----------|---------|
| `$axedir` | `'/home/siteuser/htdocs/exemplo.pt/public/axe/'` |
| `$blogparms["THEMESDIR"]` | `'/home/siteuser/htdocs/exemplo.pt/public/axethemes/'` |
| `$blogparms["POSTSDIR"]` | `'/home/siteuser/htdocs/exemplo.pt/public/'` |
| `$blogparms["PREVIEWDIR"]` | `'/home/siteuser/htdocs/exemplo.pt/public/axepreview/'` |
| `$blogparms["BLOGURL"]` | `'https://exemplo.pt/'` |
| `$blogparms["FEEDURL"]` | `'https://exemplo.pt/feed.xml'` |
| `$blogparms["PREVIEWSBASEURL"]` | `'https://exemplo.pt/axepreview/'` |

`THEMESPATH` e `THEME` mantêm-se como no projeto (`axethemes/`, `panzer3/`).

Timezone:

```php
date_default_timezone_set('Europe/Lisbon'); // ou America/Sao_Paulo
```

## 5. Nginx e `.htaccess`

O repositório inclui `.htaccess` na raiz (regras para Apache). **O Nginx do CloudPanel não interpreta `.htaccess`.**

- Bloquear listagens e acesso direto a pastas sensíveis pode ser feito com **regra personalizada no Nginx** (avançado) ou mantendo URLs que não exponham `axe/` nos links públicos.
- Para restringir `/axe/` na web, a abordagem limpa é uma **Custom Directive** no CloudPanel (se disponível na versão) ou pedido ao suporte do hosting — não edite o ficheiro do vhost sem cópia de segurança.

Em muitos casos basta **não linkar** `staging/` e `drafts/` no site; quem publica usa SSH só para CLI.

## 6. Publicar conteúdo

Por SSH, como utilizador com permissões:

```bash
cd ~/htdocs/exemplo.pt/public/axe
php axe.php -dP ../staging/meu-post.txt
```

(Ajuste `php` para o caminho completo do binário se necessário.)

**Cron** no CloudPanel: **Cron Jobs** do utilizador ou `crontab -e`:

```cron
*/15 * * * * cd /home/siteuser/htdocs/exemplo.pt/public/axe && /usr/bin/php8.3 axe.php -c >/dev/null 2>&1
```

## 7. Customização

Igual aos outros guias:

- **Identidade / URLs:** `axe_config.php`.
- **Visual:** `axethemes/<tema>/` (CSS em `css/style.css`, templates PHP do tema).
- Após mudanças grandes no tema ou URLs: `php axe.php -Rf` (ou as flags de **rebuild** da sua versão do Axe).

Consulte [uso-do-blog-axe.md](uso-do-blog-axe.md) para o fluxo `staging → draft → post`, hospedagem de **imagens/ficheiros** na pasta `public/` e **embeds** (YouTube, `<video>`, etc.).

## 8. Checklist CloudPanel + Debian 13

- [ ] Site criado no painel; domínio e SSL OK; document root = `…/public`.
- [ ] Projeto Axe copiado para dentro de `public/` com `axe/`, `axethemes/`, etc.
- [ ] `axe_config.php` com caminhos absolutos e `https://`.
- [ ] `php axe.php` (sem argumentos) corre sem erro no servidor.
- [ ] Primeiro post de teste (`-dP`) gera `index.html` e post visível no browser.
- [ ] Backup: CloudPanel ou snapshots da VPS + cópia da pasta `axe/descriptors/` e da raiz.

## Debian 13 em concreto

Debian 13 corresponde ao codename **trixie** no ciclo de lançamentos Debian. Pacotes de sistema (`apt`) seguem as mesmas ideias do Debian 12; diferenças são sobretudo versões mais recentes de PHP/Nginx nos repositórios **se** instalar pacotes extra à parte. Com **apenas** CloudPanel, confie nas versões que o instalador do painel suporta para Debian 13 e use o PHP CLI indicado pelo painel ou `php -v` na shell.

---

**Ver também:** [instalacao-debian-12.md](instalacao-debian-12.md) · [uso-do-blog-axe.md](uso-do-blog-axe.md) · [customizacao-completa-axe.md](customizacao-completa-axe.md)
