<?php
namespace Es;
require __DIR__.'/../../../vendor/autoload.php';
use Elasticsearch\ClientBuilder;
class AbstractEsModel{

    protected $_client;
    private $_hosts;

    protected $_index ="";
    protected $_type = "";

    public function __construct()
    {
        $this->_hosts = \Yaf_Registry::get('dbconfig')['es']; 
	    $clientBuilder = ClientBuilder::create();   // Instantiate a new ClientBuilder
        $clientBuilder->setHosts($this->_hosts)->setRetries(2);    // Set the hosts
        $this->_client = $clientBuilder->build();
        if(!$this->_index){
            exit("index or type is not empty");
        }
    }

    public function addIndex($mapping=array()){
        if(empty($this->_index)){
            return false;
        }
        $params['index'] = $this->_index;
        if(!empty($mapping)){
            $params['body']['mappings'] = $mapping['body'];
        }
//        $params['type'] = $this->_type;
        $result = $this->_client->indices()->create($params);
	return $result['acknowledged'];
    }

    public function updateMapping($params){
        return $this->_client->indices()->putMapping($params);
    }

    public function delIndex(){
        if(empty($this->_index)){
            throw new \Exception("no index name");
        }
        $result = $this->_client->indices()->delete(array('index'=>$this->_index));
        return $result['acknowledged'];
    }
    public function putIndexMapping($params){
        if(empty($params['index'])){
            return false;
        }
        $result = $this->_client->indices()->putMapping($params);
        return $result['acknowledged'];
    }

    public function insertData($params){
        if(empty($params['index'])){
            $params['index'] = $this->_index;
        }
        if(empty($params['type'])){
            $params['type'] = $this->_type;
        }

        if(empty($params['body'])){
            throw new \Exception("body empty");
        }
        $result = $this->_client->index($params);
        return $result;
    }

    /**
     * @param $params
     * @param int $return   1 true or false 2.todo
     * @return bool
     * @throws Exception
     */
    public function insertBatchData($params,$return = 1){
        if(empty($params['body'])){
            throw new \Exception("batch insert params body empty");
        }
        $batchParams = array();
        foreach ($params['body'] as $value){

            $batchParams['body'][] = array(
                'index' => [
                    '_index' =>empty($params['index'])?$this->_index:$params['index'],
                    '_type'  => empty($params['type'])?$this->_type:$params['type'],
                ]
            );

            $batchParams['body'][] = $value;

        }

        $result = $this->_client->bulk($batchParams);
        if($return == 1){
            $ret = $result['errors']?true:false;
        }
        return $ret;
    }

    public function get($params){
        if(empty($params['index'])){
            $params['index'] = $this->_index;
        }
        if(empty($params['type'])){
            $params['type'] = $this->_type;
        }
        if(empty($params['relate_id'])){
            throw new \Exception("id empty");
        }
    }


}
