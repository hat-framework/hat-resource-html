<?php

class drawPaginator{
    
    protected static $imprime = true;
    protected static $saved   = array();
    protected static $model   = '';
    protected static $html    = null;
    private   static $type    = "singlepagePaginator";
    
    public static function setHtmlObj($html){
        self::$html = $html;
    }
    
    public static function imprime(){
        self::$imprime = false;
    }
    
    public static function setSaved($saved){
        self::$saved = $saved;
        if(isset($saved[self::$model]['pagtype'])){
            self::setType($saved[self::$model]['pagtype']."Paginator");
        }else self::setType(self::$type);
    }
    
    public static function setModel($model){
        self::$model = $model;
    }
    
    private static function setType($type){
        $class = "$type";
        $file  = dirname(__FILE__) . "/paginator/$class.php";
        if(!file_exists($file)) $file  = dirname(__FILE__) . "/paginator/".self::$type.".php";
        //die($file);
        require_once $file;
        self::$type = $class;
    }
    
    public static function drawPages($page, $total, $arr, $table){
        $class = self::$type;
        return call_user_func("$class::drawPages", $page, $total, $arr, $table);
    }
}

?>