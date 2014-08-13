<?php

class tableResource extends \classes\Interfaces\resource {
    
    private $isprintable = true;
    private static $instance = NULL;
    /**
    * retorna a instância do banco de dados
    * @uses Faz a chamada do contrutor
    * @throws DBException
    * @return retorna um objeto com a instância do banco de dados
    */
    public static function getInstanceOf(){
        
        $class_name = __CLASS__;
        if (!isset(self::$instance)) {
            self::$instance = new $class_name;
        }

        return self::$instance;
    }
    
    public function load(){
        $this->LoadResource("html", "Html");
        if(!DISABLE_EXTERN_CSS){
            $this->Html->LoadCss('table');
        }
    }
    
    public function printable($bool = false){
    	$this->isprintable = (is_bool($bool))?$bool:false;
    }
    
    private $drawheaders = false;
    public function forceDrawHeaders(){
        $this->drawheaders = true;
    }
    
    public function draw($center, $header = array(),$footer = array(), $id = "", $class = "tablesorter"){

        if(!$this->drawheaders &&(!is_array($center) || empty ($center))) return;
        $this->load();
        if(empty($header) && !empty($center)){
            $var = end($center);
            foreach($var as $name => $value)  $header[] = $name;
        }
        
        if(empty($footer) && is_array($center) && (count($center) > 30)){
            $footer = $header;
        }
        
        $id = ($id == "")? "id='table'": "id='".  strip_tags($id)."'";
        $this->flush = "<div class='tabela'>";
            $this->flush .= "<table $id class='$class'>";
                $this->flush .= "<thead>";
                    $this->headers($header);
                $this->flush .= "</thead>";
                $this->flush .= "<tbody>";
                    if(is_array($center) && !empty ($center)) $this->imprime_centro($center, $header);
                $this->flush .= "</tbody>";
                if(!empty($footer)){
                    $this->flush .= "<tfoot>";
                        $this->headers($footer);
                    $this->flush .= "</tfoot>";
                }
            $this->flush .= "</table>";
        $this->flush .= "</div>";
        
        if($this->drawheaders) $this->drawheaders = false;
        if(!$this->isprintable) return $this->flush;
        echo $this->flush;
    }
   
    private function headers($array){
        if(empty ($array)) return;
        $extra = "";
        if(array_key_exists("class", $array)){
            $extra .= " class='".$array['class']."'";
            unset($array['class']);
        }
        
        if(array_key_exists("__id", $array)){
            $extra .= " id='".$array['__id']."'";
            unset($array['__id']);
        }
        
        if(empty ($array)) return;
        $this->flush .=  "<tr$extra>";
        foreach($array as $v){
            $class = GetPlainName($v);
            $this->flush .=  "<td class='$class'>".ucfirst($v)."</td>";
        }
        $this->flush .=  "</tr>";
    }

    private function imprime_centro($array, $header){
        
        $class = "";
        foreach($array as $value){
            if(!is_array($value)) continue;
            $cls = $id = $link = "" ;

            if(array_key_exists("class", $value)){
                $cls = " ".$value['class'];
                unset($value['class']);
            }

            if(array_key_exists("__id", $value)){
                $id = " id='".$value['__id']."'";
                unset($value['__id']);
            }

            $myclass = "";
            if(array_key_exists("__class", $value)){
                $myclass = $value['__class'];
                unset($value['__class']);
            }

            if(array_key_exists("__link", $value)){
                $link = $value['__link'];
                unset($value['__link']);
            }
            $url = "";
            if($link != ""){
                $url = $this->Html->getActionLinkIfHasPermission($link, "");
                if($url != ""){
                    $link = $this->Html->getLink($link);
                    $url  = "<a href='$link' class='links_table'>";
                }
            }
            
            $class = ($class == "")? "dif": "";
            $cls   = ($class == "")? $cls : "$cls dif";
            $this->flush .=  "<tr class='$class$myclass'$id>";

            foreach($value as $name => $val){
                
                $style = "";
                if(is_numeric(str_replace(array(","), "", $val))){
                    //$style = "style='text-align:right'";
                }
                $cls = GetPlainName($name);
                $this->flush .=  "<td class='$cls' $style>";
                if($url != "") { $this->flush .= $url;}
                $this->flush .= $val;
                if($url != "") { $this->flush .= "</a>";}
                $this->flush .=  "</td>";
            }
            

            $this->flush .=  "</tr>";
        }
        
    }

    public function drawScopo($header, $id){
        
        if(!is_array($header) || empty ($header)){
            return;
        }
        $id = ($id == "")? "id='table'": "id='$id'";
        //$this->Html->LoadCss('basic/table.css');
        $this->flush =  "<div class='tabela'>";
            $this->flush .=  "<table $id class='tablesorter'>";
                $this->flush .=  "<thead>";
                    $this->headers($header);
                $this->flush .=  "</thead>";
                $this->flush .=  "<tbody>";
                $this->flush .=  "</tbody>";
                $this->flush .=  "<tfoot>";
                    $this->headers($header);
                $this->flush .=  "</tfoot>";
            $this->flush .=  "</table>";
        $this->flush .=  "</div>";
        
        if(!$this->isprintable) return $this->flush;
        echo $this->flush;
    }
}

?>
