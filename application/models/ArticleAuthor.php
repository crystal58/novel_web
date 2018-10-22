<?php
class ArticleAuthorModel extends AbstractModel {
    const AUTHOR_STATUS = 1;     //正常
    const AUTHOR_STATUS_DEL = -1; //删除

    protected $_table = "article_author";
    protected $_database = "account";
    protected $_primary = "id"; 



    public function replaceAuthor($data){
        if(empty($data['author_name'])){
            return false;
        }
        $authorId = $data['author_id'];
        unset($data['author_id']);

        if($authorId > 0){
            return $this->update($data,array("id"=>$authorId));
        }

        return $this->insert($data);
    }

    /**
     * isCount   是否返回count
     */
    public function getList($params,$offset = 0,$pageSize = null,$isCount = false) {
        if(empty($params) && empty($pageSize)){
            return array();
        }
        $where = array();
        foreach($params as $key=>$value){
            $where['AND'][$key] = $value;
        }

        $result = array();
        if($isCount){
            $result['cnt'] = $this->count($where);
        }
        if($pageSize){
            $where['LIMIT'] = array($offset,$pageSize);
        }
        $where['ORDER'] = array(
            "id"=>"ASC"
        );
        $result['list'] = $this->fetchAll($where);
        return $result;
    }

    public function getAllAuthor(){
        $param = array(
            "AND" => array(
                "status"=>self::AUTHOR_STATUS
            ),
            "ORDER" => array(
                "id" => "ASC"
            )
        );
        return $this->fetchAll($param);
    }

}
