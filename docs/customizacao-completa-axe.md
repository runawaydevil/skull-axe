# Customização completa do Axe

Este guia descreve **onde** e **como** alterar cada camada do blog quando queres controlar o máximo possível — identidade, HTML, CSS, feed RSS, capa, página de artigo, arquivos mensais e extensões em PHP **sem** misturar com o núcleo (`axe_lib.php`, `single.php`, …) sempre que houver alternativa mais segura.

**Pré-requisitos:** já sabes publicar posts ([uso-do-blog-axe.md](uso-do-blog-axe.md)). Instalação em servidor: [instalacao-debian-12.md](instalacao-debian-12.md) ou [instalacao-debian-13-cloudpanel.md](instalacao-debian-13-cloudpanel.md).

---

## 1. Camadas de customização (por ordem recomendada)

| Camada | Onde | O que controlas |
|--------|------|-----------------|
| **A.** Config global | `axe/axe_config.php` | Título, URLs, pastas, tema ativo, contagens da capa/feed, timezone. |
| **B.** Tema (visual + markup) | `axethemes/<nome-do-tema>/` | Layout de **todas** as páginas geradas: cabeçalho, rodapé, capa, artigo, feed, arquivo mensal, CSS, imagens do tema. |
| **C.** Ficheiros opcionais no tema | Mesma pasta do tema | Menu lateral, HTML extra, blocos de anúncio (se existirem ficheiros). |
| **D.** Plugins | `axe/plugins/*.php` | Filtrar ou substituir HTML em pontos fixos (capa, post, feed, variáveis). |
| **E.** Núcleo do Axe | `axe/*.php` | Motor, catálogos, rebuild — **só** se precisares de comportamento novo que plugins não cobrem; mantém fork ou patch documentado. |

Regra prática: **A → B → D → E**. Clonar o tema (`panzer3`) para um nome novo evita perder referência quando atualizares o projeto.

---

## 2. Configuração (`axe_config.php`)

Copia `axe_config_exemplo.php` para `axe_config.php` e ajusta:

- **Identidade:** `BLOGTITLE`, `BLOGMOTTO`, `BLOGLOGO`, `BLOGOWNER`, `BLOGOWNERURL`, `BLOGTWITTER`, `BLOGOWNERTWITTER`.
- **URLs públicas (terminam em `/` ou URL completa onde indicado):** `BLOGURL`, `FEEDURL`, `PREVIEWSBASEURL`.
- **Pastas absolutas no disco:** `$axedir`, `THEMESDIR`, `POSTSDIR`, `PREVIEWDIR`, `PLUGINSDIR`.
- **Tema:** `THEMESPATH` (URL parcial, ex.: `axethemes/`), `THEME` (pasta dentro do tema, ex.: `panzer3/` ou `meu-tema/`).
- **Listagens:** `NUMPOSTSFEED`, `NUMPOSTSCOVER`, `NUMFEATSCOVER` (destaques na capa).
- **Locale:** `BLOGLOCALE`; `date_default_timezone_set(...)` no mesmo ficheiro.

### Variáveis opcionais (avançado)

Definidas **só se precisares** — o exemplo oficial nem todas lista:

| Variável | Uso |
|----------|-----|
| `EXIBIRPOPULARES` | Se `true`, o tema pode carregar `populares.php` (ficheiro opcional na pasta do tema). |
| `CSSFIXEDURL` | URL absoluta para CSS em testes locais; o `hack_the_header()` em `axe_lib.php` substitui o prefixo normal do tema por este valor nos headers. |
| `YEARLY` | Modo de URLs por **ano** em vez de `ano/mês`; valor especial `"01"` no código — só relevante se herdares um blog com essa convenção. |
| `PREVIEWFIXEDNAME` | Nome fixo do ficheiro HTML de preview (casos especiais). |

---

## 3. Criar um tema próprio (customizar “tudo” visual)

### 3.1 Clonar o tema

```bash
cp -r axethemes/panzer3 axethemes/meu-tema
```

Em `axe_config.php`:

```php
$blogparms["THEME"] = 'meu-tema/';
```

### 3.2 Ficheiros que o motor espera (panzer3 como referência)

Todos os caminhos são relativos à pasta do tema (`THEMESDIR` + `THEME`). O PHP faz `chdir` para essa pasta durante a geração.

| Ficheiro | Função |
|----------|--------|
| `header.php` | `<head>`, início do `<body>`, cabeçalho global — usado na **capa**, **tags**, **artigo** (via `hack_the_header` / substituição de placeholders). |
| `footer.php` | Fecho da página (sidebar, rodapé). |
| `single-body.php` | Corpo do **artigo** publicado (uma coluna com `%%POSTBODY%%` etc.). |
| `single-body-preview.php` | Opcional — se existir, usado no modo preview da capa no post (`try_file_get_contents`). |
| `capa-feat.php` | Cartão de **destaque** na capa. |
| `capa-post.php` | Item normal na listagem da capa (e fallback se `capa-feat` estiver vazio). |
| `capa-news.php` | Variante “notícia curta” quando o tipo de entrada é tratado como news. |
| `monthly-arch.php` | Cabeçalho/listagem de arquivo por mês. |
| `monthly-post.php` | Item dentro do arquivo mensal. |
| `feed.header`, `feed.item`, `feed.footer` | Template do **RSS** (XML). |
| `css/style.css` | Folha principal (o `header.php` do panzer3 referencia este caminho). |
| `images/` | Favicon, ícones Apple, etc. (caminhos em `header.php`). |

Ficheiros **opcionais** (se existirem, são lidos):

| Ficheiro | Função |
|----------|--------|
| `menu.php` | Linhas `texto;;url` para sidebar e menu (ver `axe_init` em `axe_lib.php`). |
| `oldarchives.html` | Bloco HTML injectável em `%%OLDARCHIVES%%`. |
| `search.php`, `midad.php`, `centerad.php`, `topad.php`, `rightad.php`, `bodyad.php` | Fragmentos para anúncios ou widgets (`%%SEARCH%%`, `%%RIGHTAD%%`, …). |
| `populares.php` | Só relevante com `EXIBIRPOPULARES` = true. |

Depois de alterar templates ou CSS, corre um **rebuild** para regenerar HTML estático:

```bash
cd axe
php axe.php -Rf
```

(Ajusta as flags de *force* à tua versão do Axe; vê [uso-do-blog-axe.md](uso-do-blog-axe.md).)

---

## 4. Placeholders (`%%NOME%%`)

Os templates usam substituição de texto:

- **Globais do blog:** `%%BLOGTITLE%%`, `%%BLOGURL%%`, `%%THEMESPATH%%`, `%%THEME%%`, `%%FEEDURL%%`, `%%LASTBUILDDATE%%`, `%%AXEVERSION%%`, etc. — construídos em `axe_init()` / listas em `axe_lib.php`.
- **Por artigo:** `%%POSTTITLE%%`, `%%POSTBODY%%`, `%%POSTDATE%%`, `%%POSTURL%%`, `%%POSTTAGS%%`, `%%POSTICON%%`, … — disponíveis quando um post está carregado.

Convém manter no `header.php` as meta tags que queres (Open Graph, Twitter); usa os placeholders já presentes no panzer3 como modelo.

---

## 5. HTML dos posts e `corrigehtml`

No corpo publicado, `corrigehtml()` em `axe_lib.php` aplica pequenas conversões (ex.: `<i>` → `<em>`, atalhos tipo `<*>`). Para markup extra no tema ou nos posts, trabalha em HTML **válido** e testa preview (`-v`).

---

## 6. Plugins (`axe/plugins/`)

Cada ficheiro `nome.php` na pasta de plugins é carregado; o **prefixo** das funções é derivado do nome do ficheiro (regex em `registra_plugins()`): usa só a parte **antes** do primeiro `_`. Por exemplo `recentcomments.php` → prefixo `recentcomments`; `meu_plugin.php` → `meu` (evita underscores no nome do ficheiro ou usa `meuplugin.php`).

O núcleo chama `nome_<tipo>(...)` quando existe:

| Tipo | Momento típico |
|------|----------------|
| `nome_blogparms` | Depois de montar `$blogparms`; pode alterar o array global. |
| `nome_postvars` | Ao preparar variáveis de um post. |
| `nome_index` | Templates da **capa**, **tags**, **arquivo mensal** — argumentos incluem troço HTML e `'header'` / `'post'` / `'footer'` / `'rebuild'` e subtipos (`feature`, `post`, `news`). |
| `nome_post` | Artigo único: `'header'`, `'body'`, `'coverpreview'`, `'footer'`. |
| `nome_feed` | `'header'`, `'item'`, `'footer'` do RSS. |

Assinatura genérica (alinhada com `aplica_plugins` em `axe_lib.php`):

```php
function meuplugin_post($trecho, $template, $p2 = '', $p3 = '', $p4 = '') {
    // $template = 'header'|'body'|'coverpreview'|'footer'
    return $trecho;
}
```

Estuda `axe/plugins/recentcomments.php` como exemplo que substitui marcadores no rodapé.

---

## 7. Extensões pontuais sem fork

- **`axe_filtraads.php`** na mesma pasta que `axe_config.php`: se existir, é incluído em `axe_init()` (filtro de anúncios legado — útil só se copiares esse ecossistema).

---

## 8. O que **não** é preciso tocar para “mudar o look”

- **`axe/descriptors/`** — geridos pelo `axe.php`; não edits à mão salvo recuperação.
- **Catálogos em `descriptors/posts/`** — gerados.
- **Raiz `index.html`, `feed.xml`, `tag-*.html`** — saída do rebuild; a fonte da verdade são tema + posts + config.

---

## 9. Checklist “customizei tudo”

- [ ] Novo tema copiado e `THEME` apontado no config.
- [ ] `header.php` / `footer.php` / `single-body.php` revistos; placeholders e links do CSS ok.
- [ ] `feed.*` validados (RSS readers, caracteres XML).
- [ ] Imagens do tema em `images/`; caminhos no HTML usam `%%BLOGURL%%%%THEMESPATH%%%%THEME%%`.
- [ ] Plugins testados com um rebuild completo.
- [ ] Um post de teste e preview antes de produção.

---

## 10. Referências no código

| Necessidade | Ficheiro |
|-------------|----------|
| Lista de includes do tema e `menu.php` | `axe/axe_lib.php` — `axe_init()` |
| Capa e rebuild | `axe/indexes.php` |
| Página de artigo | `axe/single.php` |
| Feed RSS | `axe/feed.php` + `feed.*` no tema |
| Arquivo mensal | `axe/axe_monthly.php` |
| Registo de plugins | `axe/axe_lib.php` — `registra_plugins()`, `aplica_plugins()` |

---

*Documentação alinhada ao layout do repositório atual (tema panzer3, Axe referenciado em `axe_lib.php`).*
