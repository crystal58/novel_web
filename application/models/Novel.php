<?php
class NovelModel extends AbstractModel {
    const NOVEL_STATUS_OK = 1;     //正常
    const NOVEL_STATUS_DEL = -1; //删除
    const NOVEL_STATUS_LIMIT = 2; //冻结

    const NOVEL_RECORDING_FINISH = 1;//完结
    const NOVEL_RECORDING_INIT = 0; //未录入
    const NOVEL_RECORDING_UNFINISH = 2; //录入未完结

    protected $_table = "novel";
    protected $_database = "account";
    protected $_primary = "id";

    const NOVEL_CLASS_WUXIA = 1;
    const NOVEL_CLASS_YANQING = 2;
    const NOVEL_CLASS_XIANDAI = 3;
    const NOVEL_CLASS_SCHOOL = 4;
    const NOVEL_CLASS_FANTASY = 5;
    const NOVEL_CLASS_NET = 6;
    const NOVEL_CLASS_GUDIAN = 7;

    public static $_novel_class_type = array(
        self::NOVEL_CLASS_WUXIA => "武侠",
        self::NOVEL_CLASS_YANQING => "言情",
        self::NOVEL_CLASS_XIANDAI => "现代",
        self::NOVEL_CLASS_SCHOOL => "校园",
        self::NOVEL_CLASS_FANTASY => "玄幻",
        self::NOVEL_CLASS_NET => "网络",
        self::NOVEL_CLASS_GUDIAN => "古典"
    );
    public static $_novel_class_pinxie = array(
        self::NOVEL_CLASS_WUXIA => "wuxia",
        self::NOVEL_CLASS_YANQING => "yanqing",
        self::NOVEL_CLASS_XIANDAI => "xiandai",
        self::NOVEL_CLASS_SCHOOL => "xiaoyuan",
        self::NOVEL_CLASS_FANTASY => "xuanhuan",
        self::NOVEL_CLASS_NET => "wangluo",
        self::NOVEL_CLASS_GUDIAN => "gudian",
    );

    public static $_novel_flag_txt = array(
        self::NOVEL_RECORDING_INIT => "未录入",
        self::NOVEL_RECORDING_FINISH => "完结",
        self::NOVEL_RECORDING_UNFINISH => "录入未完结"
    );

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

    public function novelList($params = array(),$offset = 0, $pageSize=0, $isCount=false,$order=array()){
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
        if($order){
            $where['ORDER'] = $order;
        }
        $where['ORDER']['id'] = "DESC";
        $result['list'] = $this->fetchAll($where);
        return $result;
    }
}
