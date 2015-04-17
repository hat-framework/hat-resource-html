<?php

class htmltable2arrayResource extends \classes\Interfaces\resource{
   
    private $header = array();
    private $assoc  = false;
    public function enableAssoc(){
        $this->assoc = true;
        return $this;
    }
    public function disableAssoc(){
        $this->assoc = false;
        return $this;
    }
    
    public function convert($contents, $callback = null){
        $DOM = new DOMDocument;
        $DOM->loadHTML($contents);

        $items = $DOM->getElementsByTagName('tr');
        if(empty($items)){return array();}
        
        $var   = array();
        foreach ($items as $node){
            $temp = ($callback === null)?$this->tdrows($node->childNodes):$callback($node->childNodes);
            if(empty($temp)){continue;}
            $var[] = $temp;
        }
        return $var;
    }
    
    private function tdrows($elements){
        $data = array();
        foreach ($elements as $i => $element){
            $value = $element->nodeValue ;
            if($this->assoc && !empty($this->header) && isset($this->header[$i])){
                $data[$this->header[$i]] = $value;
            }
            else {$data[] = $value;}
        }
        
        if($this->assoc && empty($this->header)){
            $this->header = $data;
            return ($this->assoc)?array():$data;
        }
        return $data;
    }
}

