# Instalação e customização no Debian 12 (Axe CMS)

Guia para colocar o **Axe** em um servidor ou VM **Debian 12 (bookworm)** com PHP, servidor web e permissões corretas. O site servido ao público é **HTML estático**; o PHP é usado na linha de comando (e opcionalmente pelo servidor, se você quiser rodar algo além dos `.html`).

## O que você precisa

- **PHP 8.x (CLI)** — obrigatório para `php axe.php` (publicar, rebuild, feed).
- **Servidor web** — Apache **ou** Nginx para servir os `.html`, CSS, imagens e `feed.xml`.
- **Sem banco de dados** — o Axe grava tudo em arquivos.

Extensões PHP exóticas não são necessárias para o núcleo; o pacote `php-cli` do Debian costuma bastar.

**Composer (opcional, neste fork):** se usares **Markdown** nos posts (`$blogparms["USE_MARKDOWN"] = true` em `axe/axe_config.php`), instala o [Composer](https://getcomposer.org/) na máquina onde corres `php axe.php` e, na **raiz do repositório** (pai de `axe/`), executa `composer install` para gerar `vendor/` com `league/commonmark`. O servidor web que só serve `.html` **não** precisa do Composer. Detalhes: [uso-do-blog-axe.md](uso-do-blog-axe.md), [seguranca-e-deploy.md](seguranca-e-deploy.md).

## 1. Pacotes base

Como `root` ou com `sudo`:

```bash
apt update
apt upgrade -y
```

### Opção A — Apache

```bash
apt install -y apache2 php libapache2-mod-php php-cli
systemctl enable --now apache2
```

### Opção B — Nginx + PHP só no servidor (opcional)

Para servir **apenas arquivos estáticos**, o PHP-FPM **não** é obrigatório no Nginx — basta entregar HTML. Instale o CLI para publicar:

```bash
apt install -y nginx php-cli
systemctl enable --now nginx
```

Se no futuro você adicionar scripts `.php` públicos, aí sim vale `php-fpm` e `nginx` com `fastcgi_pass`.

## 2. Estrutura de diretórios no servidor

Escolha um diretório único que será ao mesmo tempo:

- **raiz do site** (`POSTSDIR`) — onde ficam `index.html`, `feed.xml`, pastas `AAAA/MM/` dos posts;
- pasta **`axe/`** — motor;
- pasta **`axethemes/`** — temas;
- pasta **`axepreview/`** — previews (opcional em produção).

Exemplo:

```text
/var/www/meu-blog/
├── axe/              # motor (axe.php, axe_config.php, staging, drafts, descriptors…)
├── axethemes/        # temas (ex.: panzer3/)
├── axepreview/       # HTML de preview
├── index.html        # gerado pelo Axe
├── feed.xml
└── …
```

Crie o usuário/grupo de deploy (ou use `www-data`):

```bash
sudo mkdir -p /var/www/meu-blog
sudo chown -R www-data:www-data /var/www/meu-blog
```

Envie o projeto (rsync, git clone, scp) para `/var/www/meu-blog/` mantendo essa árvore.

**Permissões de escrita:** o usuário que rodar `php axe.php` precisa poder escrever em:

- `POSTSDIR` (raiz do blog)
- `axe/staging/`, `axe/drafts/`, `axe/descriptors/`, `axe/staging/old/`
- `axepreview/` (se usar preview neste servidor)
- `axedir` e subpastas que o Axe criar

O `axe_lib.php` valida diretórios graváveis ao carregar a config — se algo falhar, a mensagem de erro indica qual caminho ajustar.

## 3. Configuração `axe_config.php`

No servidor, use **caminhos absolutos Linux** e **URLs reais** (com `https://` em produção).

```bash
cp /var/www/meu-blog/axe/axe_config_exemplo.php /var/www/meu-blog/axe/axe_config.php
nano /var/www/meu-blog/axe/axe_config.php
```

Ajuste no mínimo:

| Variável | Exemplo (Linux) |
|----------|------------------|
| `$axedir` | `'/var/www/meu-blog/axe/'` |
| `$blogparms["THEMESDIR"]` | `'/var/www/meu-blog/axethemes/'` |
| `$blogparms["POSTSDIR"]` | `'/var/www/meu-blog/'` |
| `$blogparms["PREVIEWDIR"]` | `'/var/www/meu-blog/axepreview/'` |
| `$blogparms["BLOGURL"]` | `'https://blog.exemplo.org/'` |
| `$blogparms["FEEDURL"]` | `'https://blog.exemplo.org/feed.xml'` |
| `$blogparms["PREVIEWSBASEURL"]` | `'https://blog.exemplo.org/axepreview/'` |

Mantenha barras finais como no exemplo original. `THEMESPATH` e `THEME` seguem o padrão (`axethemes/`, `panzer3/`).

Timezone (já no exemplo):

```php
date_default_timezone_set('America/Sao_Paulo');
```

## 4. Apache — VirtualHost

Arquivo `/etc/apache2/sites-available/meu-blog.conf`:

```apache
<VirtualHost *:80>
    ServerName blog.exemplo.org
    DocumentRoot /var/www/meu-blog

    <Directory /var/www/meu-blog>
        AllowOverride All
        Options -Indexes
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/meu-blog-error.log
    CustomLog ${APACHE_LOG_DIR}/meu-blog-access.log combined
</VirtualHost>
```

Ative o site e o `rewrite` (o projeto traz `.htaccess` na raiz):

```bash
sudo a2ensite meu-blog.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

Aponte o DNS de `blog.exemplo.org` para o servidor. Para HTTPS, use **Certbot** (Let’s Encrypt):

```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d blog.exemplo.org
```

Depois atualize `BLOGURL`, `FEEDURL` e `PREVIEWSBASEURL` no `axe_config.php` para `https://`.

## 5. Nginx — servidor estático

Arquivo `/etc/nginx/sites-available/meu-blog`:

```nginx
server {
    listen 80;
    server_name blog.exemplo.org;
    root /var/www/meu-blog;
    index index.html;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ /\. {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/meu-blog /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

Para TLS com Certbot:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d blog.exemplo.org
```

Recrie o **rebuild** do Axe após mudar para HTTPS para que links internos e feed batam com a URL nova.

## 6. Publicar conteúdo (CLI)

Como usuário com permissão de escrita na árvore do blog:

```bash
cd /var/www/meu-blog/axe
php axe.php -d seu-texto.txt
php axe.php -P nome-do-draft.php
```

Ou em um passo: `php axe.php -dP seu-texto.txt`.

**Cron** (posts agendados): o Axe tem opções `-c` / `-C`; configure a crontab do mesmo usuário que tem permissão nos diretórios, por exemplo:

```cron
*/10 * * * * cd /var/www/meu-blog/axe && /usr/bin/php axe.php -c >/dev/null 2>&1
```

(Ajuste o intervalo e redirecionamento de log conforme sua necessidade.)

## 7. Customização

### Identidade e URLs

Tudo em `axe/axe_config.php`: `BLOGTITLE`, `BLOGMOTTO`, `BLOGLOGO`, `BLOGOWNER`, redes, `NUMPOSTSFEED`, `NUMPOSTSCOVER`, `NUMFEATSCOVER`.

### Aparência (tema)

- Arquivos em `axethemes/panzer3/` (ou copie `panzer3` para um novo nome e aponte `THEME` no config).
- CSS: `axethemes/panzer3/css/style.css`.
- Layout: `header.php`, `footer.php`, `single-body.php`, trechos `capa-*.php`, etc.

Alterou HTML/CSS? Rode um rebuild para regerar páginas que incorporam includes:

```bash
cd /var/www/meu-blog/axe
php axe.php -Rf
```

(Use a combinação de flags de **force** conforme a sua versão do Axe; no código há `-f` / `force` para rebuild completo.)

### Imagens, vídeo e embeds

O conteúdo multimédia não passa por um painel de uploads: são **ficheiros estáticos** na árvore do site (por exemplo `images/`, `videos/`) referenciados no HTML dos posts, ou URLs/embeds externos. Detalhes e exemplos de `<img>`, `<iframe>` (YouTube, etc.) e `@@POSTICON:`: [`uso-do-blog-axe.md`](uso-do-blog-axe.md) (secção *Imagens, vídeos e embeds*).

### Plugins

Diretório `axe/plugins/` — o núcleo registra plugins se existirem; veja exemplos como `recentcomments.php`.

### Proteger pastas sensíveis

Em produção, o ideal é **não** expor `axe/staging`, `axe/drafts` e descritores brutos na web. O `.htaccess` da raiz do projeto já tem regras para parte disso no Apache; no Nginx, use `location` com `deny all` para caminhos como `/axe/` se o DocumentRoot for a raiz inteira — ou mova apenas a pasta pública para o `root` do Nginx e sirva `axethemes` por symlink (layout mais trabalhoso). O modelo mais simples é o mesmo do projeto: uma raiz com HTML + `axethemes` + `axepreview`, com regras que bloqueiam acesso a `axe/`.

## 8. Checklist pós-instalação

- [ ] `php -v` e `php /var/www/meu-blog/axe/axe.php` (sem args) executam sem erro de include.
- [ ] `axe_config.php` com caminhos e URLs finais (HTTPS se usar Certbot).
- [ ] Pastas graváveis pelo usuário que publica.
- [ ] Site abre no navegador; `feed.xml` acessível.
- [ ] Teste: um post de `staging` → `-dP` → página nova e capa atualizada.

## 9. Documentação relacionada

- Uso diário (staging, drafts, Markdown/HTML, imagens e embeds): [`uso-do-blog-axe.md`](uso-do-blog-axe.md).
- **Customização completa** (tema, plugins, feed): [`customizacao-completa-axe.md`](customizacao-completa-axe.md).
- **Debian 13 + CloudPanel:** [`instalacao-debian-13-cloudpanel.md`](instalacao-debian-13-cloudpanel.md).

---

*Debian 12 — pacotes e caminhos testados conceitualmente; ajuste nomes de domínio e pastas ao seu ambiente.*
