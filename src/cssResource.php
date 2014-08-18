<?php

class cssResource extends \classes\Interfaces\resource{
   
    /**
    * retorna a instância do banco de dados
    * @uses Faz a chamada do contrutor
    * @throws DBException
    * @return retorna um objeto com a instância do banco de dados
    */
    private static $instance = NULL;
    public static function getInstanceOf(){
        $class_name = __CLASS__;
        if (!is_object(self::$instance))
            self::$instance = new $class_name();
        return self::$instance;
    }
    
    public function LoadCssIfExists($template, $theme, $media, $css, $print = true, $unique = false){
        $files[] = \classes\Classes\Registered::getTemplateLocation($template). "/themes/$theme/$css";
        $files[] = \classes\Classes\Registered::getTemplateLocation($template). "/themes/default/$css";
        $files[] = \classes\Classes\Registered::getTemplateLocation($template). "/css/$css";
        foreach($files as $file){
            //echo $file . "<br/>";
            if(!file_exists(DIR_BASIC.$file)) continue;
            $url   = URL.$file;
            if($unique) $url .= "?".  genKey('6');
            $media = ($media == '') ? 'screen': $media;
            $var = "<link rel='stylesheet' type='text/css' href='$url' media='$media'/>\n";
            if($print) {echo $var;}
            return $var;
        }
        return false;
    }
    
    public function LoadCss($template, $theme, $media, $css, $print = true, $unique = false){
        if(isset($_POST['ajax'])) return;
        $var = $this->LoadCssIfExists($template, $theme, $media, $css, $print, $unique);
        if($var === false && DEBUG){
            //echo "<div class='layout-erro'>Arquivo css $css não foi encontrado</div>";
            return "";
        }
        return $var;
    }

    public function CssTemplate($dir_src,$dir_dst, $csss){
        $file = $dir_src .$csss.".css";
        if(!file_exists($dir_src)){
            echo("Arquivo $dir_src não existe");
            return false;
        }

        $conteudo = file_get_contents($file, true);
        $conteudo = str_replace("../", '../../', $conteudo);
        $conteudo = str_replace("../../../", '../../', $conteudo);
        $fdst     = $dir_dst . $csss.".css";
        $this->LoadResource('files/file', 'fobj');
        if(!$this->fobj->savefile($fdst, $conteudo)){
            echo $this->fobj->getErrorMessage();
            return false;
        }
        return true;
    }
    
}