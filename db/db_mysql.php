<?php

/**
 * User: pmsun
 * Date: 14-5-20
 * Time: 上午12:44
 * @desc 数据库操作类，使用mysql拓展
 * @Usage
 * $config = array(
 *      'host' => 'localhost',
 *      ‘port’ => 3306,     //数据库端口号
 *      'username' => 'xxx',    //数据库用户名
 *      'password' => '',       //数据库密码
 *      'database' => 'xxx',    //数据库名
 *      'pconnect' => true/false,  //是否开启长连接
 *      ‘charset’  => 'UTF8',   //设置编码
 * );
 * $db = new Db_mysql($config);
 * //执行sql语句
 * $sql = "select `field` from `table`";
 * $db->query($sql)
 * //链式查询
 * $db->select('filed')->table()->where('')->find_all()
 * //插入数据
 * $db->insert(array('xxx' => 1, 'xxx' => 2))
 * //更新数据
 * $db->update();   //默认使用主键id来作为where条件
 * $db->where()->update();
 * //删除
 * $db->where()->del();
 * $db->del('id = 1');
 */
class Db_mysql {
    //数据库连接
    public $db;

    //数据库实例化对象
    static $instance;

    //数据库表
    public $table;

    //数据库主键
    public $pk = 'id';

    //数据库配置
    public $_config;

    //参数
    public $options = array();

    //错误信息
    protected $error;

    /**
     * 构造函数
     */
    public function __construct($config = array()) {
        $this->_config = $config;
        $this->table || $this->table = strtolower(substr(get_class($this), 0, -5));
    }

    static function instance($db_config) {
        if (!self::$instance) {
            isset($db_config['port']) && $db_config['host'] .= ":" . $db_config['port'];
            if (!$db_config['pconnect']) {
                self::$instance = @mysql_connect($db_config['host'], $db_config['username'], $db_config['password']) or exit(mysql_error(self::$instance));
            } else {
                self::$instance = @mysql_pconnect($db_config['host'], $db_config['username'], $db_config['password']) or exit(mysql_error(self::$instance));
            }
            mysql_select_db($db_config["database"]) or exit(mysql_error(self::$instance));
            mysql_query("SET NAMES {$db_config['charset']}", self::$instance);
        }
        return self::$instance;
    }

    /*
     * 禁止clone
     */
    private function __clone() {}

    /*
     * 过滤掉非数据表字段的数据
     */
    protected function _filter(& $data) {
        $fields = $this->getFiled();
        foreach ($data as $k => &$val) {
            if (in_array($k, $fields)) {
                $val = mysql_real_escape_string(str_replace("'", "\'", $val));
            } else {
                unset($data[$k]);
            }
        }
        return $data ? true : false;
    }

    /*
     * 执行sql返回数据
     */
    public function query($sql) {
        $this->db || $this->db = self::instance($this->_config);
        if (!$query = mysql_query($sql)) {
            exit(mysql_error($this->db));
        }
        $data = array();
        while ($row = mysql_fetch_assoc($query)){
            $data[] = $row;
        }
        return $data;
    }

    /**
     * 执行sql语句
     */
    public function exec($sql) {
        $this->db || $this->db = self::instance($this->_config);
        if ($result = mysql_query($sql)) {
            return $result;
        }
        return false;
    }

    /**
     * 魔术方法实现连贯操作
     */
    public function __call($methods, $args) {
        if (in_array($methods, array('select', 'table', 'where', 'limit', 'order', 'having', 'distinct', 'group', 'join', 'limit'))) {
            $this->options[$methods] = $args[0];
            return $this;
        }
    }

    /**
     * 合并一切条件
     */
    private function _options($opt) {
        $opt = array_merge($this->options, $opt);

        empty($opt['table']) && $opt['table'] = $this->table;
        empty($opt['select']) && $opt['select'] = '*';
        return $opt;

    }

    /**
     * 查询多条数据
     */
    public function find_all($opt = array()) {
        $this->db || $this->db = self::instance($this->_config);
        $opt = $this->_options($opt);
        $sql = "select {$opt['select']} from `{$opt['table']}` ";
        if (!empty($opt['where'])) {
            if (is_array($opt['where'])) {
                foreach ($opt['where'] as $k => $val) {
                    $sql .= " and `{$k}` = '{$val}' ";
                }
                $sql = preg_replace('/and/','where',$sql,1);
            } else {
                $sql .= 'where ' . $opt['where'];
            }
        }
        !empty($opt['having']) && $sql .= 'having ' . $opt['having'];
        !empty($opt['order']) && $sql .= 'order by ' . $opt['order_by'];
        !empty($opt['distinct']) && $sql .= 'distinct ' . $opt['distinct'];
        !empty($opt['group']) && $sql .= 'group by ' . $opt['group'];
        !empty($opt['limit']) && $sql .= 'limit ' . $opt['limit'];
        return $this->query($sql);
    }

    /**
     * 查询单条数据
     */
    public function find_one($opt = array()) {
        if ($result = $this->find_all($opt)) {
            return $result[0];
        }
        return false;
    }

    /**
     * 查询总数
     */
    public function get_count($opt = array()) {
        $opt = $this->_options($opt);
        $opt['select'] = "count(*) as cnt";
        if ($result = $this->find_one($opt)) {
            return $result['cnt'];
        }
        return false;
    }

    /**
     * 查询表字段
     */
    public function getFiled($table = "") {
        $table || $table = $this->table;
        $fields = array();
        if ($query = $this->exec("show full fields from `{$table}`")) {
            while ($row = mysql_fetch_assoc($query)){
                $fields[] = $row['Field'];
            }
        }
        return $fields;
    }

    /**
     * 插入数据
     */
    public function insert($data) {
        if ($this->_filter($data)) {
            $sql = "insert into `{$this->table}` (`".join('`,`', array_keys($data))."`) values ('".join("','", $data)."')";
            $this->exec($sql);
            return mysql_insert_id();
        }
        return false;
    }

    /**
     * 更新数据
     */
    public function update($data) {
        if (!$this->_filter($data)) return false;

        if (isset($data[$this->pk])) {
            $opt = array('where' => "`{$this->pk}` = {$data[$this->pk]}");
        }
        $opt = $this->_options($opt);
        if (!empty($opt['where']) && $data) {
            foreach ($data as $k => $val) {
                $set_arr[] = "`{$k}` = '{$val}'";
            }
            $sql = "update `{$opt['table']}` set " . join(",", $set_arr) . "where" . $opt['where'];
            return $this->exec($sql);
        }
        return false;
    }

    /**
     * 删除数据
     */
    public function del($opt = array()) {
        $this->db || $this->db = self::instance($this->_config);
        $opt = $this->_options($opt);
        $sql = "delete from `{$this->table}` where " . $opt['where'];
        return $this->exec($sql);
    }
}

$db = new Db_mysql(array('host' => '127.0.0.1', 'username' => 'root', 'password' => '', 'database' => 'bayuanbao', 'pconnect' => false, 'charset' => 'UTF8'));
var_dump($db->select("id")->table("byb_user")->where(array("account" => '包大人', 'passwd' => md5('1234567')))->find_all());