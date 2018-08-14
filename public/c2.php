<?php
        $config = array(
            'host' => '127.0.0.1',
            'port' => 5672,
            'login' => "guest",
            'password' => 'guest',
            'vhost' => "/"
        );
        $e_name = 'e_exchange';
        $q_name = 'q_queue_1';
       echo $r_name = 'r.routing.*';
        $conn = new AMQPConnection($config);
        if(!$conn->connect()){
            exit("connect error");
        }
        $channel = new AMQPChannel($conn);
        $ex = new AMQPExchange($channel);
        $ex->setName($e_name);
        $ex->setType(AMQP_EX_TYPE_TOPIC);
        $ex->setFlags(AMQP_DURABLE);
        $exchange = $ex->declareExchange();

        $q = new AMQPQueue($channel);
        $q->setName($q_name);
        $q->declareQueue();
        $q->bind($e_name,$r_name);
        $q->consume('message2',AMQP_AUTOACK);
        $conn->disconnect();
        function message2($e,$q){
            echo $e->getBody();
            echo "\r\n";
        }
