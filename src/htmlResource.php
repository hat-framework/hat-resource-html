<?php

class htmlResource extends \classes\Interfaces\resource{
    
    private $toInitialize = "";
    private $jqfunctions = "";
    private $jsfunctions = array();
    private $replace = "Application";
    /**
    * @uses nome do template
    */
    private $template_name = "";
    private $theme = "";
    private $started = false;
    private $addToStarted = array();

    /**
    * @uses array contendo os arquivos css já carregados
    */
    private $css_file = array();
    private $js_file  = array();
    private $jquery   = false;
    /**
    * Construtor da classe
    * @uses Carregar os arquivos necessários para o funcionamento do recurso
    * @throws DBException
    * @return retorna um objeto com a instância do banco de dados
    */
    public function __construct() {
        $this->replace = ROOT_FOLDER_NAME;
        $this->LoadResource('js/jsminifier', 'jsmin');
        $this->LoadJs("lib/html/html");
        $this->separador     = (DEBUG)?"\n\n\t":"";
        if(defined("CURRENT_TEMPLATE")) $this->template_name = CURRENT_TEMPLATE;
        else $this->template_name = "area-admin";
        return $this->load();
    }
    
    /**
    * retorna a instância do banco de dados
    * @uses Faz a chamada do contrutor
    * @throws DBException
    * @return retorna um objeto com a instância do banco de dados
    */
    private static $instance = NULL;
    public static function getInstanceOf(){
        $class_name = __CLASS__;
        if (!is_object(self::$instance))self::$instance = new $class_name();
        return self::$instance;
    }
    
    public function load(){
        $this->LoadResource('html/css', 'css');
        $template = str_replace("-", "_", $this->template_name);
        $class = $template . "Theme";
        $file = \classes\Classes\Registered::getTemplateLocation($this->template_name, true) . "/$class.php";
        
        if(file_exists($file)){
            require_once $file;
            if(class_exists($class)){
                $t = new $class();
                $this->theme = $t->getTheme();
            }
        }
    }

    public function start(){
        $this->started = true;
        //$js = URL."static/js/lib/include_once.js";
        //echo "\n<script type='text/javascript' src='$js'></script>";
        foreach($this->addToStarted as $add) echo $add;
    }
    
    /*
     * Relacionado ao tratamento de imagens
     */
    public function getImageIfExists($image, $class="", $print = true, $extra = ""){

        $image    = $this->getUrlImage($image, false);
        if($image === ""){return "";}
        $imprimir = $this->getImage($image, $class, $extra);

        if(!$print) return $imprimir;
        echo $imprimir;
    }
    
    /*
     * Relacionado ao tratamento de imagens
     */
    public function LoadImage($image, $class="", $print = true, $extra = ""){

        $image    = $this->getUrlImage($image, true);
        $imprimir = $this->getImage($image, $class, $extra);

        if(!$print) return $imprimir;
        echo $imprimir;
    }
    
    public function getUrlImage($image, $alert = true){
        $temp = "/img/" . $image;
        $file = \classes\Classes\Registered::getTemplateLocation($this->template_name, true).strtolower($temp);
        getTrueDir($file);
        if(file_exists($file)){
            return URL. \classes\Classes\Registered::getTemplateLocation($this->template_name).strtolower($temp);
        }
        
        $file = DIR_BASIC . $image;
        getTrueDir($file);
        if(!file_exists($file)){
            if($alert) {echo "<div class='erro'>erro ao carregar a imagem $file</div>";}
            return "";
        }
        return URL.APPLICATION_DIR."/".strtolower($image);
    }
    
    public function LoadExternImage($image, $class="", $print = true, $extra = ""){
        $imprimir = $this->getImage($image, $class, $extra);
        if(!$print) return $imprimir;
        echo $imprimir;
    }
    
    private function getImage($url, $class = "", $extra = ""){
        //preenche os campos
        $class       = ($class == "") ? "img"  : $class;
        $var = "<img class='$class' src='$url' $extra/>";
        return $var;
    }

    /*
     * Carregamento de css
     */
    public function addSytle($csss){
        if(trim($csss) === ""){return;};
		if(!$this->started) {$this->addToStarted[] = "<style type='text/css'>$csss</style>";}
        else echo "<style type='text/css'>$csss</style>";
    }
    
    public function loadCssIfExists($csss, $media = ""){
        //se o arquivo já foi carregado
        if(array_key_exists($csss, $this->css_file)) return;
        $this->css_file[$csss] = "";
        $css = $csss . ".css";
        return $this->css->LoadCssIfExists($this->template_name, $this->theme, $media, $css);
    }
    
    public function loadCss($csss, $media = ""){
        //se o arquivo já foi carregado
        if(!is_array($csss)) $csss = array($csss);
        foreach($csss as $cs){
            if(array_key_exists($cs, $this->css_file)) continue;
            $this->css_file[$cs] = "";
            $css = $cs . ".css";
            $var = $this->css->LoadCss($this->template_name, $this->theme, $media, $css, false);
            if($this->started) {echo $var;}
            else {
                if($cs == 'bootstrap'){array_unshift($this->addToStarted, $var);}
                else{$this->addToStarted[] = $var;}
            }
        }
    }
    
    public function loadExternCss($css, $media = "", $force = false){
        //se o arquivo já foi carregado
        if(false === $force){return;}
        if(array_key_exists($css, $this->css_file)) {return;}
        $css = str_replace(".css.css", '.css', $css . ".css");
        $this->css_file[$css] = "";
        $media = ($media == '') ? 'screen': $media;
        $var = "<link rel='stylesheet' type='text/css' href='$css' media='$media'/>\n";
        if($this->started) echo $var;
        else $this->addToStarted[] = $var;
        
    }
    
    public function getCssFile($css){
        $css  = "$css.css";
        $file = $this->css->LoadCss($this->template_name, $this->theme, "", $css, false);
        if(file_exists($file)){
            $conteudo = file_get_contents($file);
            return "<style type='text/css'/>$conteudo</style>\n";
        }
    }
    
    
    /*
     * Tratamento de links
     */
    public function getLink($url, $block_amigavel = false, $force_full_link = false){
        //echoBr($url);
        if(strstr($url, 'http://') === false && strstr($url, 'https://') === false){
            if(!isset($_SESSION['projeto']) || (defined('is_admin') && is_admin)){
                if($block_amigavel || !is_amigavel){
                    $url = ($url == "")? "": "?url=$url";
                    $url = "index.php".$url;
                }
                $url = (defined('is_admin') && is_admin) ? "admin/$url": $url;
            }
            else {
                $url = ($url == "")? "":"&url=$url";
                $url = "admin/projeto.php?projeto=".$_SESSION['projeto']."$url";
            }
            if(!defined('CURRENT_URL')) define ('CURRENT_URL', (isset($_GET['url'] )? $_GET['url']:""));
            $url = (CURRENT_URL != $url || $force_full_link)?URL.$url:"#";
        }
        return $url.getSystemParams();
    }
    
    public function getActionLinkIfHasPermission($action_url, $texto_link, $class = '', $id = "", $target = "", $extra = '', $disable_actperm = false){
        $function = "";$this->curModel = '';
        //echo "($action_url)";
        //se url não contém o prefixo http
        if((strstr($action_url, 'http://') === false && strstr($action_url, 'https://') === false)){
            if(isset($action_url[0]) && $action_url[0] != "#"){
                $temp = $action_url;
                if(!$this->LoadModel('usuario/perfil', 'perm')->hasPermission($temp)) {return "";}
            }else $action_url = CURRENT_URL . "$action_url";
            $action_url = $this->discoverModel($action_url);
        }
        
        //se o usuário estiver saindo do site
        elseif(!strstr($action_url, $_SERVER['HTTP_HOST'])){
            $class .= " link_externo";
            $target = "_blank";
            $function = "onclick='if(!confirm(\"Você está deixando o site ".SITE_NOME." e indo para o site $action_url. Deseja continuar?\")){return false;}'";
        }
        //echo "($action_url)";
        $actionp = ($disable_actperm)?"":"action_perm";
        $url     = $this->getLink($action_url);
        $trgt    = ($target == "")?"":"target='$target'";
        $active  = ($url == "#")?" active ":"";
        return "<a href='$url' id='$id' class='$actionp $active $class' $function $trgt $extra>$texto_link</a>";
    }
    
        private function discoverModel($bs){
            $bs = trim($bs);
            if(strstr($bs, 'http://') !== false || strstr($bs, 'https://') !== false){return $bs;}
            if($bs == "#" || $bs == ""){return $bs;}
            $e = explode('/', $bs);
            if(count($e) > 3){return $bs;}
            if(!isset($e[2])||$e[2] === 'index' || !isset($e[1])||$e[1] === 'index'){return $bs;}
            while(count($e) > 2){array_pop($e);}
            $this->curModel = implode("/", $e);
            $val = "";
            if(isset($_SESSION[$this->curModel])){
                $val = (is_array($_SESSION[$this->curModel]))?"/".implode("/", $_SESSION[$this->curModel]):"/{$_SESSION[$this->curModel]}";
            }
            return $bs . $val;
        }
    
    public function MakeLink($link, $text, $class="", $print = false, $print_empty_link = true, $id = ''){

        $pos  = strpos($link, 'http');
        $pos2 = (@$link[0] == "#");
        
        if($link != "" || $print_empty_link == true){
            if($pos !== false || $pos2 !== false)$url = $link;
            else $url = $this->getLink($link);
        }else $url = "#";
        
        $function = $target = "";
        if(!strstr($link, $_SERVER['HTTP_HOST'])){
            $class .= " link_externo";
            $function = "onclick='if(!confirm(\"Você está deixando o site ".SITE_NOME." e indo para o site $link. Deseja continuar?\")){return false;}'";
            $target = 'target="_blank"';
        }
        
        $text = ucfirst($text);
        $id  = ($id == "")?"":" id='$id'";
        $var = "<a href='$url' $function $target class='$class'$id>$text</a>";
        if($print) echo $var;
        else return $var;
    }
    
    
    /*
     * Tratamento de javascript
     */
    public function LoadJQueryFunction($function){
        $this->jqfunctions .= " try{ $function }catch(e){ __html.exception(e); } $this->separador";
    }
    
    public function LoadJsFunction($function, $instant = false, $type = "text/javascript", $id = ""){
        $id = ($id != "")?"id='$id'":"";
        if(!array_key_exists($type, $this->jsfunctions)) $this->jsfunctions[$type] = "";
        $this->jsfunctions[$type] .= "try{ $function } catch(e){ __html.exception(e); } $this->separador";
        /*if(!$instant && $id == ""){
            if(!array_key_exists($type, $this->jsfunctions)) $this->jsfunctions[$type] = "";
            $this->jsfunctions[$type] .= "try{ $function } catch(e){ __html.exception(e); } $this->separador";
        }
        else{
            $temp = "<script type='$type' $id> try{ $function } catch(e){ __html.exception(e); } </script>";
            if(!$this->started) $this->addToStarted[] = $temp;
            else echo $temp;
        }*/
    }
    
    public function LoadJs($jss, $instant = false, $uniqueurl = false){
        static $loaded = array();
        if(!is_array($jss)) $jss = array($jss);
        foreach($jss as $js){
            if(strstr($js, 'http') === false) {
                $tmpjs = str_replace('.js.js', '.js', $js . ".js");
                $dir   = DIR_JS.$tmpjs;
                $e     = explode($this->replace, $dir);
                $dir2  = "/{$e[1]}";
                getTrueDir($dir2);
                $dir3  = $this->auto_version($dir2);
                $js    = URL . $dir3;
                getTrueUrl($js);
            }
            if(array_key_exists($js, $this->js_file)) {continue;}
            if(array_key_exists($js, $loaded)) {continue;}
            $loaded[$js] = "";
            $js = $js . ".js";
            $js = str_replace('.js.js', '.js', $js);
            $this->js_file[$js] = "";
        }
    }
    
    public function LoadBowerComponentCss($file){
        if(!is_array($file)){$file = array($file);}
        foreach($file as &$f){
            $dir  = "js/bower_components/$f.css";
            $dir2 = $this->auto_version($dir);
            $this->loadExternCss(URL.$dir2, "", true);
        }
    }
    
    public function LoadBowerComponent($file, $css_files = array()){
        if(!is_array($file)){$file = array($file);}
        foreach($file as &$f){$f = "bower_components/$f";}
        $this->LoadJs($file, true);
        
        if(!is_array($css_files) || empty($css_files)){return;}
        $this->LoadBowerComponentCss($css_files);
    }
    
    public function getBowerComponentItem($file){
        $file = DIR_JS . "bower_components/$file";
        return(file_exists($file))?URL_JS . "bower_components/$file":"";
    }
    
    public function LoadPlugin($plugname, $files){
        if($files === "" || empty($files)){return;}
        if(!is_array($files)){$files = array($files);}
        $url = \classes\Classes\Registered::getPluginLocationUrl($plugname);
        $js  = array();
        $css = array();
        foreach($files as $f){
            $f = "$url/$f";
            if(strstr($f, '.css')){
                $css[] = $f;
            }
            else{$js[] = $f;}
        }
        if(!empty($js)){$this->LoadJs($js, false);}
        if(!empty($css)){
            foreach($css as $csss){
                $this->loadExternCss($csss, '', true);
            }
        }
    }

        /**
         *  Given a file, i.e. /css/base.css, replaces it with a string containing the
         *  file's mtime, i.e. /css/base.1221534296.css.
         *  
         *  @param $file  The file to be loaded.  Must be an absolute path (i.e.
         *                starting with slash).
         */
        public function auto_version($file){
            $filename = $_SERVER['DOCUMENT_ROOT'] .'/'. $file;
            getTrueDir($filename);
            if(!file_exists($filename)){
                return "";
            }

            $mtime = filemtime($filename);
            return preg_replace('{\\.([^./]+)$}', ".$mtime.\$1", $file);
        }
    
    private function loadAngularFile($file){
        if($file == ""){
            $this->LoadBowerComponent('angular/angular.min');
            $this->LoadBowerComponent('angular-animate/angular-animate.min');
        }
        else {
            $this->LoadJs("angular/$file");
        }
    }
    
    public function LoadAngular($file = ""){
        if(!is_array($file)){$file = array($file);}
        foreach($file as $f){
            $this->loadAngularFile($f);
        }
    }
    
    public function LoadJQuery(){
        if(isset($_REQUEST['ajax'])) return;
    	if($this->jquery) return;
        $this->jquery = true;        
        $this->LoadBowerComponent('jquery/dist/jquery.min', false);
        $this->LoadBowerComponent('jquery-migrate/jquery-migrate.min', false);
    }
    
    public function isStarted(){
        return $this->started;
    }
    
    public function getStartedArray(){
        return $this->addToStarted;
    }

    public function flush(){
        
        $this->js_file = array_keys($this->js_file);
        foreach($this->js_file as $js){
            $var = "\n<script type='text/javascript' src='$js'></script>";
            if(!$this->started) $this->addToStarted[] = $var;
            else echo $var;
        }
        $this->js_file = array();
        
        if($this->jqfunctions != ""){
            $this->toInitialize .= "$this->separador $this->jqfunctions $this->separador";
            $jqu = "$this->separador $this->jqfunctions $this->separador";
            $jss = @$this->jsfunctions['text/javascript'];
            if(!DEBUG){
                $jqu = jsminifierResource::minify($jqu);
                $jss = jsminifierResource::minify($jss);
            }
            $var = "\n
                  <script type='text/javascript'>\n
                    function __hat__html(){
                        var __html = new html();
                        $(document).ready(function() { $jqu });
                        $jss
                    }
                    __hat__html();
                    \n\n </script> \n\n";
            if(!$this->started) $this->addToStarted[] = $var;
            else echo $var;
        }elseif(isset($this->jsfunctions['text/javascript'])){
            $jss = $this->jsfunctions['text/javascript'];
            if(!DEBUG) $var = jsminifierResource::minify($jss);
            $var = "\n<script type='text/javascript'>\n $jss \n\n </script> \n\n";
            if(!$this->started) $this->addToStarted[] = $var;
            else echo $var;
        }
        foreach($this->jsfunctions as $type => $functions){
            if(!DEBUG) $functions = jsminifierResource::minify($functions);
            if($type == 'text/javascript') {continue;}
            $var = "\n
                  <script type='$type'>\n".
                  $functions.
                  "\n\n </script> \n\n";
            if(!$this->started) $this->addToStarted[] = $var;
            else echo $var;
        }
        $this->jqfunctions = "";
        $this->jsfunctions = array();
    }
    
}