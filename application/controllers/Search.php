<?php
class SearchController extends AbstractController{
    public function searchAction(){
        $search = $_GET['q'];
        if(!$search) {
            echo "搜索词为空";
            exit;
        }
        $where = array(
            'body'  => [
                'query' => [
                    'bool' => [
                        'should' => [
                            [ 'match' => [ 'author' => $search ] ],
                            [ 'match' => [ 'content' => $search ] ],
                            [ 'match' => [ 'name' => $search ] ],
                        ]
                    ]
                ],
                'highlight' => [
                    'pre_tags' => ["<em style='color: red;'>"],
                    'post_tags' => ["</em>"],
                    'fields' => [
                        "author" => new \stdClass(),
                        "content" => new \stdClass(),
                        "name" => new \stdClass()
                    ]
                ]

            ]
        );

       // var_dump($where);
        //echo json_encode($where);
        $article = new \Es\ArticleModel();
        $result = $article->search($where);
        foreach ($result['hits'] as $value){
            //var_dump($value);exit;
            echo "<div>";
            echo !empty($value['highlight']['author'][0])?$value['highlight']['author'][0]:$value['_source']['author'];
            echo " ";
            echo !empty($value['highlight']['name'][0])?$value['highlight']['name'][0]:$value['_source']['name'];
            echo " ";
            echo !empty($value['highlight']['content'][0])?$value['highlight']['content'][0]:$value['_source']['content'];
            echo "</div>";
            echo "<hr>";
        }

    }
}
