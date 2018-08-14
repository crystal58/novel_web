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

}
