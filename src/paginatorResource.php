<?php

use classes\Classes\EventTube;
class paginatorResource extends \classes\Interfaces\resource{

        static private $instance;
        private $arr = array();
        private $count;
        private $page;
        private $limit;
        private $max_numlinks;
        private $total_pages;
        private $offset;
        private $dados = array();
        private $where = "";
        private $join = "";
        private $saved = "";
        private $table = "";
        
        public static function getInstanceOf(){
            $class_name = __CLASS__;
            if (!isset(self::$instance)) self::$instance = new $class_name;
            return self::$instance;
        }

        public function setWhere($where){
            $this->where = $where;
        }
        
        public function setJoin($join){
            $this->join = $join;
        }
        
        public function setDados($dados){
            $this->dados = $dados;
        }

        public function Paginacao($table, $link, $limit = 20, $page = 1, $max_numlinks = 12){

            //carrega o total de registros da tabela
            $this->initDebugging($table);
            $this->table = $table;
            $this->count = $this->GetCount($table);
            $this->page  = (is_numeric($page))? $page : 1;
            $this->limit = $limit;
            
            $link		= $this->prepareLink($link);
            $this->link = ($link[strlen($link) -1] == "/") ? $link:"$link/";
            $max_numlinks = ($max_numlinks%2 == 1)? $max_numlinks: $max_numlinks - 1;
            $this->max_numlinks = (($max_numlinks) < 3)? 3 : $max_numlinks;
            
            $this->total_pages = 1;
            if( $this->count > 0 ) {
                $this->limit = ($this->limit > 0) ? $this->limit: 0;
                $this->total_pages = ($this->limit != 0)?ceil($this->count/$this->limit): 1;
            }
            
            if($this->page == ""){
                $this->page = 1;
            }
            
            if ($this->page > $this->total_pages) {
                $this->page = $this->total_pages;
            }
            
            $this->offset = $this->limit*$this->page - $this->limit;
            if ($this->offset < 0) $this->offset = 0;

            return $this->geraLink();
             
        }
		
				private function prepareLink($link){
					$lk      = $link;
					$urlget = $this->getUrlGet($link);
					$u      = isset($urlget['url'])?$urlget['url']:$lk;
					$mlink  = $this->LoadResource('html', 'html')->getLink('', true,true);
					$conector = "?";
					foreach($_GET as $nm => $v){
						if($nm == 'url' || array_key_exists($nm, $urlget)) {continue;}
						if(!is_array($v)){
							$mlink .= "$conector{$nm}={$v}";
							$conector = "&";
							continue;
						}
						
						foreach($v as $vvl){
							$mlink .= "{$conector}{$nm}[]={$vvl}";
							$conector = "&";
						}
					}
					
					foreach($urlget as $nm => $v){
						if($nm == 'url'){continue;}
						$mlink .= "$conector{$nm}={$v}";
						$conector = "&";
					}

					return $mlink .$conector. "url=$u";
				}
        
                private function getUrlGet($link){
                    $temp = explode("?", $link);
                    if(empty($temp)){return array();}
                    $t2   = explode("&", end($temp));

                    if(empty($t2)){return array();}
                    $out  = array();
                    foreach($t2 as $t){
                        $vv = explode("=", $t);
                        if(!isset($vv[1])){continue;}
                        $out[$vv[0]] = $vv[1];
                    }
                    return $out;
                }
        
        private $debuggin = false;
        private $force_debug = false;
        public function startDebug(){
            $this->debuggin = true;
            $this->force_debug = true;
        }
        
        public function stopDebug(){
            $this->debuggin = false;
            $this->force_debug = false;
        }
        
        public function debuggin(){
            if($this->force_debug){return true;}
            return $this->debuggin;
        }
        
        private function initDebugging($table){
            if(usuario_loginModel::IsWebmaster() && isset($_GET['paginator_debug']) && $_GET['paginator_debug'] == $table){
                $this->debuggin = true;
            }else{$this->debuggin = false;}
        }
        
        public function setPaginationType($model, $type = ''){
            $types = array('multipage', 'singlepage');
            $type  = (in_array($type, $types))?$type:'multipage';
            $this->saved[$model]['pagtype'] = $type;
        }
        
        public function selecionar($model, $campos, $where = "", $orderby = ""){
            
            $modelname = $model->getModelName();
            $this->saved[$modelname]['page']        = $this->page;
            $this->saved[$modelname]['total_pages'] = $this->total_pages;
            $this->saved[$modelname]['table']       = $this->table;
            $this->saved[$modelname]["arr"]         = $this->arr;
            
            $campos = empty ($campos)?$model->getCampos($campos):$campos;
            if(empty ($campos)) {
                $table  = str_replace("_", "/", $model->getTable());
                echo "<hr/>Paginator: Campos não configurados no modelo $table <br/> 
                Adicione itens display:true ao modelo<br/> Linha:".__LINE__."<hr/>";
            }
            
            $model->db->setJoin($this->join);
            $out = $model->selecionar($campos, $where, $this->limit , $this->offset, $orderby);
            if($this->debuggin()){
                $error     = $this->db->getErrorMessage();
                $sentenca  = $this->db->getFormatedSentenca();
                $var  = "<b>Método: </b>".__METHOD__."<br/>\n";
                $var .= "<b>Tabela: </b>$this->table<br/>\n";
                $var .= "<b>Página Atual: </b>$this->page<br/>\n";
                $var .= "<b>Total de Páginas: </b>$this->total_pages<br/>\n";
                $var .= "<hr/><b>Sql da Paginação: </b><br/>$this->pagsentenca<br/>\n";
                $var .= "<hr/><b>Sql da Seleção: </b><br/>$sentenca\n\n<hr/><br/><br/>";
                if(trim($error) !== ""){$var .= "<hr/><b>Mensagem de Erro: </b><br/>$error\n\n<hr/><br/><br/>";}
                echo($var);
                if(!empty($out)){
                    print_in_table($out);
                }else{
                    echo "<hr/> Seleção não retornou nenhum resultado!";
                }
            }
            EventTube::addEvent('paginate_'.$model->getTable(), $this->draw(false, $modelname));
            return $out;
        }
        
        public function draw($print = true, $model = ""){
            $this->dir = dirname(__FILE__);
            $this->LoadResourceFile("classes/drawPaginator.php");
            if(!isset($this->saved[$model]['pagtype'])) $this->saved[$model]['pagtype'] = 'multipage';
            drawPaginator::setHtmlObj($this->LoadResource('html', 'html'));
            drawPaginator::setModel($model);
            drawPaginator::imprime($print);
            drawPaginator::setSaved($this->saved);
            return drawPaginator::drawPages($this->page, $this->total_pages, $this->arr, $this->table);
        }

        public function getLimit(){
            return $this->limit;
        }

        public function getOffset(){
            return $this->offset;
        }
    
        private function geraLink(){
            $this->arr = array();
            $maxnlinks = ceil($this->max_numlinks/2);
            
            //a maior página a ser exibida é a ultima
            $end  = $this->page + $maxnlinks - 1;
            $end  = ($end > $this->total_pages)? $this->total_pages: $end;
            
            //as páginas começam a partir do 1
            $init = $this->page - $maxnlinks;
            $init = ($init < 1)? 1:$init;

            //seta a primeira página
            if($this->total_pages > 1){
                $mylink = $this->link ."1";
                $this->arr[$mylink] = "Primeira";
            }

            //seta as próximas páginas
            for($i = $init; $i < $end; $i++){
                $mylink = $this->link . ($i+1);
                $this->arr[$mylink] = ($i+1);
            }
            
            //seta o ultima pagina
            if($this->total_pages > 1){
                $mylink = $this->link .$this->total_pages;
                $this->arr[$mylink] = "Última";
            }
            
            
            if(usuario_loginModel::CodUsuario() == 1){
                if(isset($_GET['paginator_debug']) && $_GET['paginator_debug'] == $this->table){
                    $link = str_replace(array("&paginator_debug=$this->table", "paginator_debug=$this->table"), "", $this->link);
                    $this->arr[$link] = "Debug Disable";
                }
                else{$this->arr["$this->link&paginator_debug=$this->table"] = "Debug";}
            }
        }
        
        public function getLastCount(){
            return $this->count;
        }
        
        public function getTotalPages(){
            return $this->total_pages;
        }

        private function GetCount($table){
            if(empty ($this->dados)) $dados = "*";
            else $dados = implode(', ',$this->dados);
            $where = ($this->where == "")? "": "WHERE $this->where";
            $this->LoadResource("database", "db");
            $sentença = "SELECT COUNT( $dados ) as count FROM  `$table` $this->join $where";
            $arr = ($this->db->ExecuteQuery($sentença));
            $this->pagsentenca = $this->db->getFormatedSentenca();
            $this->where = "";
            if(empty ($arr)) return 0;
            $count = (count($arr) > 1)?count($arr):$arr[0]['count'];
            //if($this->table == "ocorrencia_mensagem")
            //die("<b>Método:</b> ".__METHOD__. "<br/><b>Sentença:</b> $sentença <br/><b>Total:</b> </b>". $count);
            return $count;
        }
}