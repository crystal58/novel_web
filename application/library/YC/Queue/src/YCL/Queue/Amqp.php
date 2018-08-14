<?php


/**
*
* AMQP 协议
*
*
*
**/
class YCL_Queue_Amqp extends YCL_Queue_Abstract {

    // 全局的配置文件
    protected $_config = null;

    // 连接
    protected $_connection = null;
    // 通道
    protected $_channel = null;
    // 队列
    protected $_queueList = null;

    protected $_lastMsg = null;

    const MESSAGE_PERSISTENT_MODE = 2;
    const DEFAULT_PORT = 5672;

    const DEFAULT_READ_TIMEOUT = 0;
    const DEFAULT_WRITE_TIMEOUT = 3;
    const DEFAULT_CONNECT_TIMEOUT = 3;
    const DEFAULT_HEARTBEAT = 10;
    
    public function __construct($config) {
        $this->_config = $config;

        if(!isset($config['connection']) || !is_array($config['connection'])) {
            throw new YCL_Queue_Exception("There is no connection config");
        }
        $options = array(
            'read_timeout' => self::DEFAULT_READ_TIMEOUT,
            'write_timeout' => self::DEFAULT_WRITE_TIMEOUT,
            'connect_timeout' => self::DEFAULT_CONNECT_TIMEOUT,
            'heartbeat' => self::DEFAULT_HEARTBEAT,
        );

        $this->_initConnection(array_merge($options, $config['connection']));

        if(isset($config['exchange']) && is_array($config['exchange'])) {
            $this->_initExchange($config['exchange']); 
        }
        if(isset($config['queue']) && is_array($config['queue'])) {
            $this->_initQueue($config['queue']);
        }
    }

    protected function _initConnection($config) {
        if(!isset($config['host'])) {
            throw new YCL_Queue_Exception("connection config host is missing"); 
        }
        $hosts = is_array($config['host']) ? $config['host'] : array($config['host']);
        
        $is_connected = false;
        $connection = null;

        //轮询可用服务器
        foreach ($hosts as $host) {
            $options = array(
                'host' => $host,
                'port' => isset($config['port']) ? $config['port'] : self::DEFAULT_PORT,
                'login' => $config['login'],
                'password' => $config['password'],
                'vhost' => $config['vhost'],
                'read_timeout' => $config['read_timeout'],
                'write_timeout' => $config['write_timeout'],
                'connect_timeout' => $config['connect_timeout'],
                'heartbeat' => $config['heartbeat'],
                'channel_max' => isset($config['channel_max']) ? $config['channel_max'] : 0,
                'frame_max' => isset($config['frame_max']) ? $config['frame_max'] : 0,
            );
            $connection = new AMQPConnection($options);
            try {
                $is_connected = $connection->connect();
                if ($is_connected) {
                    $this->_connection = $connection;
                    break;
                }           
            } catch(Exception $e) {
                error_log(__FILE__.":".__LINE__."-".$e->getMessage());
            }
        }

        if (!$is_connected) {
            throw new YCL_Queue_Exception("Connect to queue service failed");
        }

        //create a channel
        $this->_channel = new AMQPChannel($this->_connection);
    }

    protected function _initExchange($config) {
        //only support 'direct' 'fanout' 'topic', not support 'headers'
        if ($config['type'] != AMQP_EX_TYPE_DIRECT
            && $config['type'] != AMQP_EX_TYPE_FANOUT
            && $config['type'] != AMQP_EX_TYPE_TOPIC) {
            throw new YCL_Queue_Exception("not support for {$config['type']} exchange type");
        }

        //config setting       
        $exchange = new AMQPExchange($this->_channel);
        $exchange->setName($config['name']);
        $exchange->setType($config['type']);
        $exchange->setFlags(AMQP_DURABLE);

        if (!$exchange->declareExchange()) {
            throw new YCL_Queue_Exception("Declare the exchange failed");
        }
        $this->_exchange = $exchange;
    }

    /**
    *
    * @param array $config , key can be: name, routing_key, exchange_name, delay
    *   name: string queue name
    *   routing_key: string | array 
    *   exchange_name : string 
    *   delay : int seconds to delay
    *
    * @return
    **/
    protected function _initQueue($config) {
        foreach($config as $cfg) {
            if(!isset($cfg['name']) || !isset($cfg['routing_key']) || !isset($cfg['exchange_name'])) {
                throw new YCL_Queue_Exception("name/routing_key/exchange_name cannot be empty");
            }
            $queue = new AMQPQueue($this->_channel);

            //config setting
            $queue->setName($cfg['name']);
            $queue->setFlags(AMQP_DURABLE);
            //Returns the message count
            $queue->declareQueue();

            $keys = isset($cfg['routing_key']) ? $cfg['routing_key'] : array();
            $keys = is_array($keys) ? $keys : array($keys);
            foreach($keys as $key) {
                $queue->bind($cfg['exchange_name'], $key);
            }

            // 如果定义了 delay 配置，则认为是延时队列，自动创建延时 Queue
            /*
            if(isset($cfg['delay']) && $cfg['delay'] > 0) {
                foreach($keys as $key) {
                    $q = new AMQPQueue($this->_channel);
                    $q->setName("delay:{$cfg['name']}:$key");
                    $q->setFlags(AMQP_DURABLE);
                    $args = array(
                            'x-dead-letter-exchange' => $cfg['exchange_name'],
                            'x-dead-letter-routing-key' => $key,
                            'x-message-ttl' => $cfg['delay'] * 1000,
                            );
                    $q->setArguments($args);
                    $q->declareQueue();
                    $q->bind($cfg['exchange_name'], "delay:" . $key); 
                }
            }
            */

            $this->_queueList[$cfg['name']] = $queue;
        }
    }
    /**
    *
    * @param string $key  routing key
    * @param mixed $value
    * @param int $pri
    * @param int $delay seconds
    * @param int $ttr
    *
    * @return
    *
    **/
    public function push($key, $value, $pri = null, $delay = null, $ttr = null) {
        if(!$this->_exchange instanceof AMQPExchange) {
            throw new YCL_Queue_Exception("exchange config is empty");
        }

        $value = serialize($value);
        $options = array(
            'delivery_mode' => self::MESSAGE_PERSISTENT_MODE
            );
        $routingKey = $key;
        if($delay > 0) {
            $routingKey = "delay.$key.$delay";
            $exchangeName = $this->_exchange->getName();
            $q = new AMQPQueue($this->_channel);
            $q->setName($routingKey);
            $q->setFlags(AMQP_DURABLE);
            $args = array(
                    'x-dead-letter-exchange' => $exchangeName,
                    'x-dead-letter-routing-key' => $key,
                    'x-message-ttl' => intval($delay * 1000),
                    );
            $q->setArguments($args);
            $q->declareQueue();
            $q->bind($exchangeName, $routingKey);
        }
        return $this->_exchange->publish($value, $routingKey, AMQP_NOPARAM, $options);
    }
    

    /**
    * pop ttl key from the queue
    *
    * @param string $key  queue name
    * @param int $delay seconds
    *
    * @return mixed
    **/
    public function popTTL($key, $delay){
        if(empty($delay)){
            throw new YCL_Queue_Exception("delay is empty");
        }
        if(!$this->_exchange instanceof AMQPExchange) {
            throw new YCL_Queue_Exception("exchange config is empty");
        }

        $options = array(
            'delivery_mode' => self::MESSAGE_PERSISTENT_MODE
            );
        $routingKey = $key;
        $routingKey = "delay.$key.$delay";
        $exchangeName = $this->_exchange->getName();
        $q = new AMQPQueue($this->_channel);
        $q->setName($routingKey);
        $q->setFlags(AMQP_DURABLE);
        $args = array(
                'x-dead-letter-exchange' => $exchangeName,
                'x-dead-letter-routing-key' => $key,
                'x-message-ttl' => intval($delay * 1000),
                );
        $q->setArguments($args);
        $q->declareQueue();
        //$q->bind($exchangeName, $routingKey);
        $msg = $q->get(AMQP_AUTOACK);

        if(empty($msg)) {
            return false;
        }

        $this->_lastMsg = $msg;

        return unserialize($msg->getBody());
    }

    /**
    * pop key from the queue
    *
    * @param string $key  queue name
    *
    * @return mixed
    **/
    public function pop($key){
        $this->_lastMsg = null;
        $queue = $this->_queueList && isset($this->_queueList[$key]) ? $this->_queueList[$key] : null;

        if(!$queue) {
            throw new YCL_Queue_Exception("queue config is empty");
        }

        $msg = $queue->get(AMQP_AUTOACK);

        if(empty($msg)) {
            return false;
        }

        $this->_lastMsg = $msg;

        return unserialize($msg->getBody());
    }

    public function begin() {
        $this->_channel->startTransaction();
    }

    public function commit() {
        $this->_channel->commitTransaction();
    }

    public function rollback() {
        $this->_channel->rollbackTransaction();
    }

    /**
    * 使用阻塞+回调函数的方式来处理消息
    * XXX 请仔细阅读代码中的 ABCDE 5 段注释，了解该函数的消息处理机制
    *
    * @param string   $key queue name
    * @param callable $callback
    * @param array    $options
    *
    * @return
    **/
    public function consume($key, callable  $callback, $options = array()) {
        if(empty($key)) {
            throw new YCL_Queue_Exception("key cann't be empty:" . __FILE__);
        }

        $times = 0;
        $begin = time();
        $queue = $this->_queueList && isset($this->_queueList[$key]) ? $this->_queueList[$key] : null;

        if($queue === null) {
            throw new YCL_Queue_Exception("cann't find queue for $key in " . __FILE__);
        }

        $queue->consume(function($envelope, $q) use (&$times, $begin, $callback, $options) {
            $errorHandler = isset($options['error_handler']) ? $options['error_handler'] : '';
            $maxTimes = isset($options['max_times']) ? (int) $options['max_times'] : 0;
            $maxTimeLength = isset($options['max_time_length']) ? (int) $options['max_time_length'] : 0;
            $times ++;
            $key = $envelope->getRoutingKey();
            $data = unserialize($envelope->getBody());

            // A. 处理消息次数超过设定，则将消息重新放回队列，退出进程
            if($maxTimes > 0 && $times > $maxTimes) {
                $q->nack($envelope->getDeliveryTag(), AMQP_REQUEUE);
                return false;
            }
            // B. 处理消息时间超过设定，则将消息重新放回队列，退出进程
            if($maxTimeLength > 0 && time() - $begin > $maxTimeLength) {
                $q->nack($envelope->getDeliveryTag(), AMQP_REQUEUE);
                return false;
            }

            try {
                // C. 返回 false, 按照 官方 consume 返回值处理机制， 正常退出，其他情况，不退出
                $result = $callback($key, $data);
                $q->ack($envelope->getDeliveryTag());
                return $result;
            // D. 明确异常退出，将消息放回队列，并且退出进程
            } catch (YCL_Queue_ExitException $e) {
                $q->nack($envelope->getDeliveryTag(), AMQP_REQUEUE);
                if($errorHandler && is_callable($errorHandler)) {
                    $errorHandler($key, $e);
                }
                return false;
            // E. 抛出其他异常，将消息放回队列，暂时会退出进程，将来可能会改为不退出进程 XXX
            } catch (Exception $e) {
                // XXX 如果将队列放回去，可能会导致队列阻塞，暂时直接丢弃消息 by guoxiaod 20150121
                //$q->nack($envelope->getDeliveryTag(), AMQP_REQUEUE);
                $q->nack($envelope->getDeliveryTag());

                if($errorHandler && is_callable($errorHandler)) {
                    $errorHandler($key, $e);
                }

                return false;
            }
        });
    }
}
