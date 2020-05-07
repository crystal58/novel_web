<?php
class SearchController extends AbstractController{
    public function searchAction(){
	echo 333;
        $search = new \Es\ArticleModel();
//$r = $search->createIndex();
  //      var_dump($r);
        $r = $search->updateMappingData();
        var_dump($r);
    }
}
