<?php

class guiResource extends \classes\Interfaces\resource {

    private static $loaded = array();
    public function exec($component, $data, $options = array()){
        if(!isset(self::$loaded[$component])){
            self::$loaded[$component] = $this->LoadGuiComponent($component);
        }
        if(self::$loaded[$component] === null){return;}
        $obj = self::$loaded[$component];
        return $obj->draw($data, $options);
    }
    
    private function LoadGuiComponent($component){
        $e     = str_replace("/", "\\", $component);
        $class = "templates\\".CURRENT_TEMPLATE."\hat\gui\\{$e}GUI";
        $file = classes\Classes\Registered::getTemplateLocation(CURRENT_TEMPLATE, true) . "/hat/gui/{$component}GUI.php";
        getTrueDir($file);
        if(file_exists($file)){
            require_once $file;
        }
        return (class_exists($class))?new $class():null;
    }
    
}
