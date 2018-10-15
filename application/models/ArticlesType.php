<?php
class ArticlesTypeModel extends AbstractModel {

    const ARTICLE_CLASS_STATUS = 1;     //正常
    const ARTICLE_CLASS_STATUS_DEL = -1; //删除

    protected $_table = "article_type";
    protected $_database = "account";
    protected $_primary = "id";

    const ARTICLE_TYPE_TANG = 1;
    const ARTICLE_TYPE_SONG = 2;

    /**
     * isCount   是否返回count
     */
    public function getList($params) {
        $where = array();
        foreach($params as $key=>$value){
            $where['AND'] = array(
                $key => $value
            );
        }
        $where['ORDER'] = array(
            "chapter_order"=>"ASC",
            "id" => "ASC"
        );
        $result['list'] = $this->fetchAll($where);
        return $result;
    }

    public function getAllClass(){
        $param = array(
            "AND" => array(
                "status"=>self::ARTICLE_CLASS_STATUS
            ),
            "ORDER" => array(
                "id" => "DESC"
            )
        );
        return $this->fetchAll($param);
    }

}
