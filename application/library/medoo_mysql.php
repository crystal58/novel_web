<?php

require __DIR__ . '/medoo.php';

class medoo_mysql extends medoo {

    const SLOW_QUERY_LOG = '/var/log/nginx/slow_query.log';
    const DEFAULT_SLOW_QUERY_TIME = 2;

    protected $debug = false;

    public function __construct($options = null) {
        $this->debug = isset($options['debug']) ? $options['debug'] : false;
        if (is_array($options)) {
            unset($options['debug']);
        }
        parent::__construct($options);

        $this->_slowQueryTime = defined("SLOW_QUERY_TIME") ? constant("SLOW_QUERY_TIME") : 2;
    }

    protected function select_context($table, $join, &$columns = null, $where = null, $column_fn = null) {
        $foundRows = false;
        if (is_array($where) && isset($where['FOUND_ROWS'])) {
            $foundRows = $where['FOUND_ROWS'];
            unset($where['FOUND_ROWS']);
        }
        if (is_array($columns) && isset($columns['FOUND_ROWS'])) {
            $foundRows = $columns['FOUND_ROWS'];
            unset($columns['FOUND_ROWS']);
        }
        $sql = parent::select_context($table, $join, $columns, $where, $column_fn);
        return $foundRows ? ("SELECT SQL_CALC_FOUND_ROWS " . substr($sql, 7)) : $sql;
    }

    public function found_rows() {
        $ret = $this->query("select FOUND_ROWS()")->fetchColumn();
        return is_numeric($ret) ? $ret + 0 : 0;
    }

    protected function column_quote($string) {
        if ($string[0] == '#') {
            return str_replace('.', '"."', preg_replace('/(^#|\(JSON\))/', '', $string));
        } else {
            return '"' . str_replace('.', '"."', preg_replace('/(^#|\(JSON\))/', '', $string)) . '"';
        }
    }

    protected function column_push($columns) {
        if ($columns == '*') {
            return $columns;
        }

        if (is_string($columns)) {
            $columns = array($columns);
        }

        $stack = array();

        foreach ($columns as $key => $value) {
            preg_match('/[#]?([a-zA-Z0-9_\-\.]*)\s*\(([a-zA-Z0-9_\-]*)\)/i', $value, $match);

            if (isset($match[1], $match[2])) {
                array_push($stack, $this->column_quote($match[1]) . ' AS ' . $this->column_quote($match[2]));
            } else {
                array_push($stack, $this->column_quote($value));
            }
        }

        return implode($stack, ',');
    }

    public function batch_insert($table, $datas) {
        // Check indexed or associative array
        if (!isset($datas[0])) {
            $datas = array($datas);
        }

        $all_values = array();
        foreach ($datas as $data) {

            $values = array();

            foreach ($data as $key => $value) {

                switch (gettype($value)) {
                    case 'NULL':
                        $values[] = 'NULL';
                        break;

                    case 'array':
                        preg_match("/\(JSON\)\s*([\w]+)/i", $key, $column_match);

                        $values[] = isset($column_match[0]) ?
                                $this->quote(json_encode($value)) :
                                $this->quote(serialize($value));
                        break;

                    case 'boolean':
                        $values[] = ($value ? '1' : '0');
                        break;

                    case 'integer':
                    case 'double':
                    case 'string':
                        $values[] = $this->fn_quote($key, $value);
                        break;
                }
            }
            $all_values[] = '(' . join(', ', $values) . ')';
        }
        $sql = join(",", $all_values);
        $columns = array_map(array($this, "column_quote"), array_keys($datas[0]));
        $this->exec('INSERT INTO "' . $table . '" (' . implode(', ', $columns) . ') VALUES ' . $sql);
        $lastId = $this->pdo->lastInsertId();

        $lastIds = array();
        for ($i = 0, $count = count($datas); $i < $count; $i++) {
            $lastIds[] = $lastId + $i;
        }

        return $lastIds;
    }

    protected function fn_quote($column, $string) {
        return isset($column[0]) && $column[0] == '#' ? $string : $this->quote($string);
    }

    public function query($query,$map=[]) {
        $begin = microtime(true);
        $ret = $this->pdo->query($query);
        $end = microtime(true);
        $time = $end - $begin;
        if($time > $this->_slowQueryTime) {
            $this->_writeSlowLog($time, $query, $begin);
        }
        return $ret;
    }

    public function exec($query,$map=[]) {
        $begin = microtime(true);
        //$ret = $this->pdo->exec($query);
        $ret = parent::exec($query,$map);
        $time = microtime(true) - $begin;
        if($time > $this->_slowQueryTime) {
            $this->_writeSlowLog($time, $query, $begin);
        }
        return $ret;
    }

    public function begin() {
        $this->pdo->beginTransaction();
    }

    public function commit() {
        $this->pdo->commit();
    }

    public function rollback() {
        $this->pdo->rollback();
    }

    public function __destruct() {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollback();
        }
    }

    private function _checkPath($file) {
        $dir = dirname($file);
        if(!file_exists($dir)) {
            $old = umask(0000);
            @mkdir($dir, 0777, true);
            umask($old);
        }
    }

    private function _writeSlowLog($time, $query, $begin) {
        $date = date("Y-m-d H:i:s", $begin);
        $str = "====== $date ======\n";
        $str .= "Spend: $time s\n";
        $str .= "SQL: " . substr($query, 0, 1024) . "\n";
        $rows = debug_backtrace();
        foreach($rows as $k => $r) {
            $file = isset($r['file']) ? $r['file'] : '';
            $line = isset($r['line']) ? $r['line'] : 0;
            $str .= "$k: {$file}:{$line}\n";
        }
        $suffix = date("Ymd", $begin);
        $file = self::SLOW_QUERY_LOG . '-' . $suffix;
        $this->_checkPath($file);
        $old = umask(0111);
        @file_put_contents($file, $str, FILE_APPEND);
        umask($old);
    }

}
