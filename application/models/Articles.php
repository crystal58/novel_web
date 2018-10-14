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
    public function getList($params) {
        $where = array();
        foreach($params as $key=>$value){
            $where['AND'] = array(
                $key => $value
            );
        }
        $result['list'] = $this->fetchAll($where);
        return $result;
    }


}
