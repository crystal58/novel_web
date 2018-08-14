<?php
class NovelTmpModel extends AbstractModel {
    const NOVEL_TMP_STATUS_INIT = 0;
    const NOVEL_TMP_STATUS_READY = 1;
    const NOVEL_TMP_STATUS_FINISH = 2;
    const NOVEL_TMP_STATUS_FAILTURE = -1;
    const NOVEL_TMP_STATUS_DEL = -2;

    protected $_table = "novel_tmp";
    protected $_database = "account";
    protected $_primary = "id";

    public static $_novel_tmp_status = array(
      self::NOVEL_TMP_STATUS_FAILTURE => "失败",
      self::NOVEL_TMP_STATUS_INIT => "初始化",
      self::NOVEL_TMP_STATUS_READY => "就绪",
      self::NOVEL_TMP_STATUS_FINISH => "完成"
    );

    /**
     * isCount   是否返回count
     */
    public function getList($params,$offset = 0,$pageSize = null,$isCount = false) {
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

        $result['list'] = $this->fetchAll($where);
        return $result;
    }

    public function getCount($params){
        if(empty($params) && empty($pageSize)){
            return array();
        }
        foreach($params as $key=>$value){
            $where['AND'] = array(
                $key => $value
            );
        }

        $count = $this->count($where);
        return $count;
    }


}
