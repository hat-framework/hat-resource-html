<?php

class multipagePaginator extends drawPaginator{
    
    public static function drawPages($page, $total, $arr, $table){
        $model = self::$model;
        if($model != "" && array_key_exists($model, self::$saved)){
            $page  = self::$saved[$model]['page'];
            $total = self::$saved[$model]['total_pages'];
            $arr   = self::$saved[$model]["arr"];
            //$table = self::$saved[$model]["table"];
        }
        
        if($page == 1 || $page == 0) $page = "Primeira";
        elseif($page == $total)$page = "Última";

        $var = "";
        foreach($arr as $link => $num){
            $link   = self::$html->getLink($link, true);
            $class  = ($page == $num)?"class='atual'":"";
            $active = ($page == $num)?"class='active'":"";
            $var .= "<li $active><a href='$link#$table' $class>$num</a></li>";
        }
        if($var == "") return "";
        $out = "<div class='paginator' id='pag_$table'><ul class='pagination'>$var</ul></div>";
        if(!self::$imprime) return $out;
        echo $out;
    }
}