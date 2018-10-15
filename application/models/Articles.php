<?php
class ArticlesModel extends AbstractModel {

    const ARTICLE_CLASS_STATUS = 1;     //正常
    const ARTICLE_CLASS_STATUS_DEL = -1; //删除

    protected $_table = "articles";
    protected $_database = "account";
    protected $_primary = "id";

    /**
     * isCount   是否返回count
     */
    public function getList($params,$offset = 0,$pagesize = false) {
        $where = array();
        foreach($params as $key=>$value){
            $where['AND'] = array(
                $key => $value
            );
        }
        if($pagesize){
            $where['LIMIT'] = array($offset,$pagesize);
        }

        $result['list'] = $this->fetchAll($where);
        return $result;
    }


}
