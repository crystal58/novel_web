<?php
class NovelChapterModel extends AbstractModel {


    protected $_table = "novel_chapters";
    protected $_database = "account";
    protected $_primary = "id";


    public function replaceNovel($data){
        if(empty($data['name'])){
            return false;
        }
        $novelId = $data['novel_id'];
        unset($data['novel_id']);
        if($novelId > 0){
            return $this->update($data,array("id"=>$novelId));
        }
        return $this->insert($data);
    }

    public function chaptersList($params = array(),$offset = 0, $pageSize=20, $isCount=false){
        if(empty($params) && empty($pageSize)){
            return array();
        }
        $where = array();
        foreach($params as $key=>$value){
            $where['AND'] = array(
                $key => $value
            );
        }

        $result = array();
        if($isCount){
            $result['cnt'] = $this->count($where);
        }
        if($pageSize){
            $where['LIMIT'] = array($offset,$pageSize);
        }
        $where['ORDER'] = array(
            "chapter_order"=>"ASC"
        );
        $fields = array("id","title");
        $result['list'] = $this->fetchAll($where,$fields);
        return $result;
    }

    public function chapter($params){
        if(empty($params)) return "";
        foreach($params as $key=>$value){
            $where['AND'] = array(
                $key => $value
            );
        }
        $result = $this->fetchRow($where);
        return $result;

    }

}
