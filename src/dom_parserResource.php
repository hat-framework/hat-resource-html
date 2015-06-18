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
    
    public function parseTable2Array($html, $parseText){
        if($html == "") {return $this->setErrorMessage("Caro usuário, o html enviado para parsing está vazio!");}
        $obj = $this->parseStart($html);
        if(is_array($obj) && empty($obj)){return false;}
        
        $lines = $this->getLines($obj, $parseText);
        if($lines === false){return false;}
        
        return $this->getArray($lines);
    }
    
            private function parseStart($html){
                $obj   = $this->parseHtml($html);
                if(!is_object($obj) || $obj === false){
                    $this->setErrorMessage("Erro ao encontrar os links no parseHtml");
                    return array();
                }
                return $obj;
            }
            
            private function getLines($obj, $parseText){
                $links = $obj->find("$parseText > tbody > tr");
                if(false === $links){
                    return $this->setErrorMessage("Erro ao encontrar '$parseText'");
                }
                return $links;
            }
            
            private function getArray($lines){
                $i   = $key = 0;
                $arr = array();
                foreach($lines as $line){
                    $i = 0;
                    while($data = $line->find('td', $i)){
                        if($data === false || !is_object($data)){break;}
                        $arr[$key-1][$i] = trim(strip_tags($data->innertext));
                        $i++;
                    }
                    $key++;
                }
                return $arr;
            }
                    
}