<?php
namespace Es;
class ArticleModel extends AbstractEsModel
{
    protected $_index = "article_index";
//    protected $_type = "article_type";

    public function createIndex()
    {
        $result = $this->addIndex($this->updateMappingData());
        return $result;
    }

    private function updateMappingData(){
        $mapping = array(
            'body' => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => [
                        'article_id' => [
                            'type' => 'integer'
                        ],
                        'name' => [
                            'type' => 'text',
                            'analyzer' => 'ik_max_word'
                        ],
                        'content' => [
                            'type' => 'text',
                            'analyzer' => 'ik_max_word'
                        ],
                        'author' => [
                            'type' => 'text',
                            'analyzer' => 'ik_max_word'
                        ],
                        'author_id' => [
                            'type' => 'integer'
                        ]

                    ]
            ]
        );
        return $mapping;

    }

    public function search($params){
        if(empty($params['index'])){
            $params['index'] = $this->_index;
        }
        //var_dump($params);
        $result = $this->_client->search($params);
        $hits = array();
        if(isset($result['hits'])){
            $hits = $result['hits'];
        }

        if($hits && $hits['total']>0 && !empty($hits['hits'])){
            return $hits;
        }
        return false;
    }

    public function aggsSearch($params){

        $result = $this->_client->search($params);
        $aggregations = array();

        if(isset($result['aggregations']['log_over_time']) && $result['hits']['total']>0){
            $aggregations = $result['aggregations']['log_over_time'];
            $aggregations['total'] = $result['hits']['total'];
            return $aggregations;
        }

        return $aggregations;
    }
}
