<?php
class SearchController extends AbstractController{
    public function searchAction(){
        $search = new \Es\ArticleModel();
$r = $search->createIndex();
        var_dump($r);
    }
}
