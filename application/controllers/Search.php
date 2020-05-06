<?php
class SearchController extends AbstractController{
    public function searchAction(){
        $search = new \Es\ArticleModel();
        $r = $search->addIndex();
        var_dump($r);
        $r = $search->updateMappingData();
        var_dump($r);
    }
}
