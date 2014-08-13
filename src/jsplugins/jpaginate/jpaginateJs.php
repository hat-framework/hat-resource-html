<?php

use classes\Classes\JsPlugin;
class jpaginateJs extends JsPlugin{
    
    static private $instance;
    public static function getInstanceOf($plugin){
        $class_name = __CLASS__;
        if (!isset(self::$instance)) self::$instance = new $class_name($plugin);
        return self::$instance;
    }
    
    public function init(){        
        $this->Html->LoadJs("$this->url/js/jquery.paginate");
    }    
    
    public function draw($id, $count = 10){
$var = <<<VARR
        var i = $count;
        var id = '.$id';
        $(id).hide();
        $(id).each(function(){
            i = i-1;
            if(i < 0) return;
            $(this).show();
        });
        var total_$id = $(id).length;
        var count_$id = Math.ceil(total_$id/$count);
        if(total_$id > $count){
            var texto = '1 - $count de '+ total_$id;
        }else{
            $('#paginate_$id, #paginate_count_$id').hide();
        }
        $('#paginate_count_$id').html(texto);
        $("#paginate_$id").paginate({
            count     : count_$id,
            start     : 1,
            display   : $count,
            mouse     : 'press',
            onChange  : function(page){
               var lastshow = page * $count; 
               if(lastshow > total_$id) lastshow = total_$id;
               var firstshow = lastshow - $count + 1;
               var texto = firstshow + ' - ' + lastshow + ' de '+ total_$id;
               $('#paginate_count_$id').html(texto);
               $(id).hide();
               i = 0;
               $(id).each(function(){
                    i = i + 1;
                    if(i > lastshow) { return; }
                    if(i >= firstshow){ $(this).show(); }
                });
           }
        });
VARR;
        $this->Html->LoadJqueryFunction($var);
        echo "<div id='paginate_$id'></div><div id='paginate_count_$id'></div>";
    }
}

?>