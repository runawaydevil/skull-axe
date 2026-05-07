<?php
/*
Arquivo de configuração do Axe para o seu blog

© 2013  Augusto Campos http://augustocampos.net/ (9.05.2013)
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
global $blogparms;
global $axedir;

// edite as variáveis abaixo de acordo com a configuração do seu blog e servidor

# título e descrição breve do blog
$blogparms["BLOGTITLE"] = 'Meu Blog';
$blogparms["BLOGMOTTO"] = "Testando o CMS AXE... e aprovando";
$blogparms["BLOGLOGO"] = "http://seu-site.com/images/bloglogo.png";

# URL da raiz do blog, terminando com /
$blogparms["BLOGURL"] = 'http://seu-site.com/blog/';
# twitter do blog, sem @
$blogparms["BLOGTWITTER"] = 'blogtwitter';
# URL de acesso ao feed
$blogparms["FEEDURL"] = 'http://seu-site.com/blog/feed.xml';

# Dados de identificação do autor do blog: nome, URL, twitter (sem @) 
$blogparms["BLOGOWNER"] = 'Nome do dono deste blog';
$blogparms["BLOGOWNERURL"] = 'http://url-do-dono.com/';
$blogparms["BLOGOWNERTWITTER"] = 'donotwitter';

# quantidade de posts no feed e na capa
$blogparms["NUMPOSTSFEED"] = '10';
$blogparms["NUMPOSTSCOVER"] = '10';
$blogparms["NUMFEATSCOVER"] = '2'; // numero de destaques na capa	

# diretório em que está o axe no servidor:
$axedir='/users/xxx/www/blog/axe/';

# caminho completo do diretório dos temas no servidor
$blogparms["THEMESDIR"] = '/users/xxx/www/blog/axethemes/';

# caminho web dos temas (em relação à raiz do blog). Começa sem e termina com /
$blogparms["THEMESPATH"] = 'axethemes/';

# pasta do tema do blog (dentro do THEMESDIR/THEMESPATH)
$blogparms["THEME"] = 'panzer3/';

# diretório-base dos plugins
$blogparms["PLUGINSDIR"] = $axedir.'plugins/';


# diretório onde constam os posts publicados em HTML, os indices, tags e feed
$blogparms["POSTSDIR"] = '/users/xxx/www/blog/';

# Caminho web da raiz até o diretório de posts. Começa sem "/" e termina com "/"
# O caso normal é esta variável ficar vazia. TO DO: documentar o uso.
$blogparms["POSTSURLPREFIX"] = ''; 


# diretório onde são gravados os previews (em HTML) de posts
$blogparms["PREVIEWDIR"] = '/Users/augusto/Dropbox/axe-cms/brlinux/axepreview/';

# A URL completa do diretório de previews. Termina com "/"
$blogparms["PREVIEWSBASEURL"] = 'http://seu-site.com/blog/axepreview/'; 


# idioma e localização
$blogparms["BLOGLOCALE"] = 'pt_BR';	
# mensagens do PHP que devem ser exibidas
error_reporting(E_ALL ^ E_NOTICE ^ E_USER_NOTICE);
# encontre o nome correto da sua timezone 
# em: http://php.net/manual/en/timezones.php
date_default_timezone_set('America/Sao_Paulo');

?>
