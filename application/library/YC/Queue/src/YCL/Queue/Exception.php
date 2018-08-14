<?php

class YCL_Queue_Exception extends RuntimeException
{
    const GENERAL_ERROR = 1;
    const CONNECTION_ERROR = 2;
    const CHANNEL_ERROR = 3;
    const EXCHANGE_ERROR = 4;
    const QUEUE_ERROR = 5;
}// END OF CLASS
