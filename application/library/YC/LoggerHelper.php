<?php

namespace YC;

class LoggerHelper {

    const LOGERPREFIX = '/var/log/noval/';

    private static $_logs = [];
    private static $logLevel = [
        LOG_EMERG => 'EMERG',
        LOG_CRIT => 'CRIT',
        LOG_ERR => 'ERROR',
        LOG_WARNING => 'WARNING',
        LOG_NOTICE => 'NOTICE',
        LOG_INFO => 'INFO',
        LOG_DEBUG => 'DEBUG',
    ];

    public static function GetLogs() {
        $log = self::$_logs;
        self::$_logs = [];
        return $log;
    }

    public static function clearLogs() {
        self::$_logs = [];
    }

    public static function EMERG($partition, $logData = array(), $logData2 = array()) {
        self::_log($partition, LOG_EMERG, func_get_args());
    }

    public static function CRIT($partition, $logData = array(), $logData2 = array()) {
        self::_log($partition, LOG_CRIT, func_get_args());
    }

    public static function ERR($partition, $logData = array(), $logData2 = array()) {
        self::_log($partition, LOG_ERR, func_get_args());
    }

    public static function WARNING($partition, $logData = array(), $logData2 = array()) {
        self::_log($partition, LOG_WARNING, func_get_args());
    }

    public static function NOTICE($partition, $logData = array(), $logData2 = array()) {
        self::_log($partition, LOG_NOTICE, func_get_args());
    }

    public static function INFO($partition, $logData = array(), $logData2 = array()) {
        self::_log($partition, LOG_INFO, func_get_args());
    }

    public static function DEBUG($partition, $logData = array(), $logData2 = array()) {
        self::_log($partition, LOG_DEBUG, func_get_args());
    }

    private static function _log($partition, $logType = LOG_INFO, $logArgs = array()) {
        array_shift($logArgs);
        //未定义debug模式时，当log的级别大于信息6（LOG_DEBUG => 'DEBUG',7）
        if (!(defined('DEBUG') && DEBUG > 0) && $logType > LOG_INFO) {
            return;
        }

        $logLevel = isset(self::$logLevel[$logType]) ? self::$logLevel[$logType] : '';
        $logs = ['time' => date("Y-m-d H:i:s"), 'LEVEL' => $logLevel];

        foreach ($logArgs as $logData) {
            foreach ((array) $logData as $key => $value) {
                if ($key === 'DEBUG') {
                    $value = '<<<<' . join("\t", (array) $value) . '>>>>';
                } else {
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                    }
                }
                $value = str_replace("/\b+/", ' ', $value);
                if (is_int($key)) {
                    $logs[] = $value;
                } else {
                    $logs[] = $key . ':' . $value;
                }
            }
        }
        $logStr = join("]\t[", $logs) . "\n";
        self::_cacheLog($partition, $logLevel, $logArgs);
        self::_writeLog($partition, $logLevel, $logStr);
        if ($logType <= LOG_ERR) {
            error_log($logStr);
        }
    }

    private static function _getFilePath($partition, $logLevel) {
        return self::LOGERPREFIX . $partition . "_" . $logLevel . "_log-" . date("Ymd");
    }

    private static function _writeLog($partition, $logLevel, $logStr) {
        $logFile = self::_getFilePath($partition, $logLevel);
        if(!file_exists($logFile)) {
            self::_mkfile($logFile);
        }
        if(PHP_SAPI == 'cli') {
            file_put_contents($logFile, $logStr, FILE_APPEND);
        } else {
            file_put_contents($logFile, $logStr, FILE_APPEND);
        }
    }

    private static function _cacheLog($partition, $logLevel, $log) {
        if (!defined('DEBUG') || DEBUG <= 0) {
            return;
        }
        if ('cli' == PHP_SAPI) {
            print_r($log);
            return;
        }
        if (count(self::$_logs) < 40) {
            self::$_logs[] = [$partition . '---' . $logLevel, $log];
        }
    }

    private static function _mkfile($logFile) {
        if (!file_exists($logFile)) {
            $logFolder = dirname($logFile);
            if (!is_dir($logFolder)) {
                if (!self::Mkdirs($logFolder)) {
                    return false;
                }
            }
            touch($logFile);
            chmod($logFile, 0666);
        }
        return true;
    }

    private static function Mkdirs($dir) {
        if (!is_dir($dir)) {
            if (!self::Mkdirs(dirname($dir))) {
                return false;
            }
            if (!mkdir($dir, 0777)) {
                return false;
            } else {
                chmod($dir, 0777);
            }
        }
        return true;
    }

}
