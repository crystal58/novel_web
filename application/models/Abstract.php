<?php
use YC\Db\ManageDb;
class AbstractModel{
    protected $_table;
    protected $_database;
    protected $_primary = "id";
    protected $_farmCount = 0;
    public function __construct(){
        if(empty($this->_table) || empty($this->_database) || empty($this->_primary)){
            throw new \Exception("table database primary can mot empty");
        }
        $this->_primary = is_array($this->_primary)? $this->_primary : array($this->_primary);
    }

    public function find($id,$isWrite = false){
        $ids = is_array($id) ? $id : array($id);
        $where = array();
        foreach($ids as $pKey=>$value){
            $where[$this->_primary[$pKey]] = $value;
        }
        return $this->_getRead($this->_farm($id),$isWrite)->get($this->_table,"*",array("AND" => $where));        
    }
    public function fetchRow($where,$field="*",$id=null,$join=null,$isWrite=false){
        return $this->_getRead($this->_farm($id),$isWrite)->get($this->_table,$field,$where);
    }
    public function fetchAll($where,$field="*" ,$id=null,$join=null,$isWrite=false){
        return $this->_getRead($this->_farm($id),$isWrite)->select($this->_table,$field,$where);
    }

   private function _getWrite($farm = null){
        return ManageDb::getWrite($this->_database,$farm);
   }

   private function _getRead($farm = null, $isWrite = false){
        if($isWrite){
            return $this->getWrite($this->_database,$farm);
        }
        return ManageDb::getRead($this->_database,$farm);
   }
   private function _farm($id){
       $ids = is_array($id) ? array_shift($id) : $id;
       return $this->_farmCount === 0 ? null : ($id % $this->_farmCount) + 1;
   }
   public function __call($method,$args){
       $methods = array("count","sum","min","max","avg");
       if(in_array($method,$methods)){
           array_unshift($args,$this->_table);
           $obj = $this->_getRead($this->_farm(null));
           return call_user_func_array(array($obj,$method),$args);
       }
       throw new \Exception(get_called_class()." has no method $method");
   }
   public function insert($data, $id = null) {
       return $this->_getWrite($this->_farm($id))->insert($this->_table, $data);
   }
    public function update($data, $where, $id = null) {
        return $this->_getWrite($this->_farm($id))->update($this->_table, $data, $where);
    }

    public function delete($where, $id = null) {
        return $this->_getWrite($this->_farm($id))->delete($this->_table, $where);
    }
    public function batchInsert($datas, $id = null) {
        return $this->_getWrite($this->_farm($id))->batch_insert($this->_table, $datas);
    }
}
