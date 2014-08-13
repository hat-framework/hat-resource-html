<?php

class dom_parserResource extends \classes\Interfaces\resource{
    
    private static $instance = NULL;
    public static function getInstanceOf(){
        $class_name = __CLASS__;
        if (!is_object(self::$instance)){
            self::$instance = new $class_name();
        }
        return self::$instance;
    }
    
    private function init(){
        $this->dir = dirname(__FILE__);
        $this->LoadResourceFile("dom_parser/simple_html_dom.php");
    }
    
    public function parseUrl($url){
        $this->init();
        return  file_get_html($url);
    }
    
    public function parseHtml($str){
        $this->init();
        return str_get_html($str);
    }
    
    public function getDom($str){
        $lowercase       =true; 
        $forceTagsClosed =true; 
        $target_charset  = DEFAULT_TARGET_CHARSET; 
        $stripRN         =true;
        $defaultBRText   =DEFAULT_BR_TEXT; 
        $defaultSpanText =DEFAULT_SPAN_TEXT;
        $dom = new simple_html_dom(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
         
        if (empty($str) || strlen($str) > MAX_FILE_SIZE){
            $dom->clear();
            return false;
        }
        $dom->load($str, $lowercase, $stripRN);
        return $dom;
    }
}