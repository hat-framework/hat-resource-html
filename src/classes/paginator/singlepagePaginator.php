<?php

class singlepagePaginator extends drawPaginator{
    
    public static function drawPages($page, $total, $arr, $table){
        $obj = new Object();
        $res = $obj->LoadResource('html', 'html');
        $res->LoadJqueryFunction("
            $('.load_older').live('click', function(event){
                event.preventDefault();
                var url = $(this).attr('href').split('/');
                url[url.length-1] = parseInt(url[url.length-1])+ 1;
                url = url.join('/');
                $(this).attr('href', url);
                var container = '#' + ($(this).parent().parent().attr('id'));
                $(this).parent().remove();
                $.ajax({
                    url: url,
                    data:{'ajax':'true'},
                    type:'post',
                    dataType: 'html',
                    success: function(json) {
                        $(container).append(json);
                    }
                });
                
            });
        ");
        $model = self::$model;
        if($model != "" && array_key_exists($model, self::$saved)){
            $page  = self::$saved[$model]['page'];
            $total = self::$saved[$model]['total_pages'];
            $arr   = self::$saved[$model]["arr"];
        }
        
        if($page == $total) {return;}
        if($page == 1 || $page == 0) $page = "Primeira";
        
        $var = "";
        foreach($arr as $link => $num){
            if($page != $num) continue;
            $link = self::$html->getLink($link, true);
			die($link);
            $class = ($page == $num)?"class='atual'":"";
            $var = "<a href='$link' class='atual load_older'>Mostrar mais antigas</a>";
            break;
        }
        if($var == "") return "";
        $out = "<div class='paginator' id='pag_$table'>$var</div>";
        if(!self::$imprime) return $out;
        echo $out;
    }
}

?>