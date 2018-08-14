<?php

class TestController extends AbstractController{
    
   public $config = array(
            'host' => '127.0.0.1',
            'port' => 5672,
            'login' => "guest",
            'password' => 'guest',
            'vhost' => "/"
   ); 
    public function productorAction(){
        $config = array(
            'host' => '127.0.0.1',
            'port' => 5672,
            'login' => "admin",
            'password' => 'admin',
            'vhost' => "/"
        );
        $e_name = 'e_exchange';
        $q_name = 'q_queue';
        $r = $this->get("r","");
        echo $r_name = "r".($r?".$r":"");
        $conn = new AMQPConnection($this->config);
        if(!$conn->connect()){
            exit("connect error");
        }
        $channel = new AMQPChannel($conn);
        $ex = new AMQPExchange($channel);
        $ex->setName($e_name);
        $ex->setType(AMQP_EX_TYPE_TOPIC);
        $ex->setFlags(AMQP_DURABLE);
        $exchange = $ex->declareExchange();
        //$q = new AMQPQueue($channel);
        //$q->setName($q_name);
        //$queue = $q->declareQueue();
        //$q->setName("q_queue_1");
        //$q->declareQueue();
        //$ex->publish("hello","r_routing");
        //$ex->publish("hi","r.topic");
        $ex->publish("hello",$r_name);


    }

    public function consumerAction(){
      $e_name = 'e_exchange';
        $q_name = 'q_queue';
        $r_name = 'r_routing';
        $conn = new AMQPConnection($this->config);
        if(!$conn->connect()){
            exit("connect error");
        }
        $channel = new AMQPChannel($conn);
        $ex = new AMQPExchange($channel);
        $ex->setName($e_name);
        $ex->setType(AMQP_EX_TYPE_FANOUT);
        $ex->setFlags(AMQP_DURABLE);
        $exchange = $ex->declareExchange();

        $q = new AMQPQueue($channel);
        $q->setName($q_name);
        $q->bind($e_name,$r_name);
        $q->consume('message',AMQP_AUTOACK);
        $conn->disconnect();
   
    }

    public function indexAction(){
//        phpinfo();    
    }

    public function getContentAction(){
        $novelTmp = new NovelTmpModel();
        $where = array(
            "AND" => array(
                "status" => 0
            ),
            "LIMIT" => array(0,100)
        );
        $list = $novelTmp->fetchAll($where);
//echo json_encode($list);exit;
        foreach ($list as $value){
            if(empty($value['title']))continue;
            //var_dump($value);
            $url = $value["url"];
            $data = file_get_contents($url);
            $data = iconv('gb2312','UTF-8//IGNORE',$data);
            $content = json_decode($value['content_url'],true);
            $postContent = $content['content'];
            preg_match("#$postContent#is",$data, $contentRet);
            echo $contentRet[$content['num']];
            exit;
        }
    }
}

function message($envelope , $queue){
   echo 44443333;exit;
    var_dump($envelope->getRoutingKey);
    $msg = $envelope->getBody(); 
    echo $msg."\n";
}


