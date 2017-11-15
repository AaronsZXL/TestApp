<?php
//header("Content-type: text/html; charset=utf-8");
error_reporting(E_ALL^E_NOTICE^E_STRICT);

require_once('./config.php');                    //云服务器用这句
//require_once($_SERVER['DOCUMENT_ROOT'].'/phpcode/config.php');  //SAE 用这句
Class DB {
 
    private $link_id;
    private $handle;
    private $is_log;
    private $time;
 	private $charset;

    //构造函数
    public function __construct() {
        $this->time = $this->microtime_float();
        $this->charset=ConfigHelper::$charset;
        $this->connect(ConfigHelper::$hostname, ConfigHelper::$username,ConfigHelper::$password, ConfigHelper::$database, ConfigHelper::$pconnect,ConfigHelper::$MySQLType);
        $this->is_log = ConfigHelper::$log;
        if($this->is_log){
            //$handle = fopen(ConfigHelper::$logfilepath.date("Ymd")."dblog.txt", "a+");
            $handle = fopen(dirname(__FILE__)."/".ConfigHelper::$logfilepath."/".date("Ymd")."dblog.txt", "a+");
            $this->handle=$handle;
        }
    }
     
    //数据库连接
    public function connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect = 0,$MySQLType="MSSQL2008") {
        if ($MySQLType=="SAE"){
            if ($pconnect==0){
                	$this->link_id =@mysql_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT, SAE_MYSQL_USER, SAE_MYSQL_PASS,true);
                    if(!$this->link_id){
                	$this->halt("SAE数据库连接失败");
           	 			}
            } else {
        			$this->link_id =@mysql_pconnect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT, SAE_MYSQL_USER, SAE_MYSQL_PASS);
                    if(!$this->link_id){
                	$this->halt("SAE数据库持久连接失败");
            			}
            }
            if(!@mysql_select_db($dbname,$this->link_id)) {
                $this->halt('数据库选择失败');
            }
            @mysql_query("set names ".$this->charset);
        }
        else if ($MySQLType=="MSSQL2008"){
            $opt = array('Database'=>ConfigHelper::$database,
                         'CharacterSet'=>ConfigHelper::$charset,
                         'UID'=>ConfigHelper::$username,
                         'ReturnDatesAsStrings'=>true,
                         'PWD'=>ConfigHelper::$password);

            if ($pconnect==1 && is_resource($this->link_id)) return;

            if(is_resource($this->link_id)) sqlsrv_close($this->link_id);
            $this->link_id = null;

            $this->link_id = sqlsrv_connect(ConfigHelper::$hostname.','.ConfigHelper::$port,$opt);
            if(!$this->link_id){
                $this->halt("MSSQL2008数据库连接失败");
            }
        }
        else { //MySQL连接
        	if( $pconnect==0 ) {
            	$this->link_id = @mysql_connect($dbhost, $dbuser, $dbpw, true);
            	if(!$this->link_id){
                	$this->halt("数据库连接失败");
           	 			}
        	} else {
            	$this->link_id = @mysql_pconnect($dbhost, $dbuser, $dbpw);
            	if(!$this->link_id){
                	$this->halt("数据库持久连接失败");
            			}
        	}
            if(!@mysql_select_db($dbname,$this->link_id)) {
                $this->halt('数据库选择失败');
            }
            @mysql_query("set names ".$this->charset);
        }

    }
     
    //查询 
    public function query($sql) {
        $this->write_log("查询 ".$sql);
        if (ConfigHelper::$MySQLType=="MSSQL2008"){
            $params = array();
            $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
            $query = sqlsrv_query($this->link_id,$sql, $params, $options);
            if(!$query) 
            {
                // $errmsg="";
                // if( ($errors = sqlsrv_errors() ) != null) {
                //     foreach( $errors as $error ) {
                //         $errmsg.= "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
                //         $errmsg.= "code: ".$error[ 'code']."<br />";
                //         $errmsg.= "message: ".$error[ 'message']."<br />";
                //     }
                // }
                $this->halt('Query Error: ' . $sql);
            }
            return $query;
        }
        else {
            $query = mysql_query($sql,$this->link_id);
            if(!$query) $this->halt('Query Error: ' . $sql);
            return $query;
        }
    }
     
    //获取一条记录（MYSQL_ASSOC，MYSQL_NUM，MYSQL_BOTH QLSRV_FETCH_ASSOC, SQLSRV_FETCH_NUMERIC,SQLSRV_FETCH_BOTH               
    public function get_one($sql,$result_type = MYSQL_ASSOC) {
        $query = $this->query($sql);
        if (ConfigHelper::$MySQLType=="MSSQL2008"){
            $rt=sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC); //这里暂时写 SQLSRV_FETCH_ASSOC
        }
        else{
            $rt =& mysql_fetch_array($query,$result_type);
        }
        $this->write_log("获取一条记录 ".$sql);
        return $rt; 
    }
    //从结果集获取一条记录
    public function get_one_record($result)
    {
        if (ConfigHelper::$MySQLType=="MSSQL2008")
            return sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
        else
    	   return mysql_fetch_assoc($result);
    } 
    //获取全部记录
    public function get_all($sql,$result_type = MYSQL_ASSOC) {
        $query = $this->query($sql);
        $i = 0;
        $rt = array();
        if (ConfigHelper::$MySQLType=="MSSQL2008"){
            while($row =sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC)) {
                $rt[$i]=$row;
                $i++;
            }
        }
        else {
            while($row =& mysql_fetch_array($query,$result_type)) {
                $rt[$i]=$row;
                $i++;
            }
        }
        $this->write_log("获取全部记录 ".$sql);
        return $rt;
    }
    
    //插入
    public function insert($table,$dataArray) {
        $field = "";
        $value = "";
        if( !is_array($dataArray) || count($dataArray)<=0) {
            $this->halt('没有要插入的数据');
            return false;
        }
        while(list($key,$val)=each($dataArray)) {
            $field .="$key,";
            $value .="'$val',";
        }
        $field = substr( $field,0,-1);
        $value = substr( $value,0,-1);
        $sql = "insert into $table($field) values($value)";
        $this->write_log("插入 ".$sql);
        if(!$this->query($sql)) return false;
        return true;
    }
 

    public function insert_OutWithID($table,$dataArray) {
        $field = "";
        $value = "";
        if( !is_array($dataArray) || count($dataArray)<=0) {
            $this->halt('没有要插入的数据');
            return false;
        }
        while(list($key,$val)=each($dataArray)) {
            $field .="$key,";
            $value .="'$val',";
        }
        $field = substr( $field,0,-1);
        $value = substr( $value,0,-1);
        $sql = "insert into $table($field) values($value) ";
        $this->write_log("插入 ".$sql);
        $result=$this->query($sql);

        if ($result)
        {
            $sql="select IDENT_CURRENT('$table')";
            return $this->getsingle($sql);
        }
        else return 0;

    }

    //更新
    public function update( $table,$dataArray,$condition="") {
        if( !is_array($dataArray) || count($dataArray)<=0) {
            $this->halt('没有要更新的数据');
            return false;
        }
        $value = "";
        while( list($key,$val) = each($dataArray))
        $value .= "$key = '$val',";
        $value = substr( $value,0,-1);
        $sql = "update $table set $value where 1=1 ";
 		if ($condition!="") $sql=$sql." and $condition ";
        $this->write_log("更新 ".$sql);
        if(!$this->query($sql)) return false;
        return true;
    }
 
    //删除
    public function delete( $table,$condition="") {
        if( empty($condition) ) {
            $this->halt('没有设置删除的条件');
            return false;
        }
        $sql = "delete from $table where 1=1 and $condition";
        $this->write_log("删除 ".$sql);
        if(!$this->query($sql)) return false;
        return true;
    }
 
    //返回结果集
    public function fetch_array($query, $result_type = MYSQL_ASSOC){
        $this->write_log("返回结果集");
        if (ConfigHelper::$MySQLType=="MSSQL2008")
            return sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC);
        else
            return mysql_fetch_array($query, $result_type);
    }
 
    //获取记录条数
    public function num_rows($results) {
        if(!is_bool($results)) {
            if (ConfigHelper::$MySQLType=="MSSQL2008")
                $num = sqlsrv_num_rows($results);
            else
                $num = mysql_num_rows($results);
            $this->write_log("获取的记录条数为".$num);
            return $num;
        } else {
            return 0;
        }
    }
 
    //获取单个字段值
    public function getsingle ($sqlstr)
    {
        $result=$this->query($sqlstr);
        if ($this->num_rows($result)==1){
            if (ConfigHelper::$MySQLType=="MSSQL2008"){
                $row = sqlsrv_fetch_array($result,SQLSRV_FETCH_NUMERIC);
                if(is_array($row) && count($row)==1)
                    return $row[0];
                else 
                    return false;
            }
            else{
                return mysql_result($result,0);
            }
        }
        else
            return false;  
    }
    //释放结果集
    public function free_result() {
        $void = func_get_args();
        foreach($void as $query) {
            if(is_resource($query) && get_resource_type($query) === 'mysql result') {
                return mysql_free_result($query);
            }
        }
        $this->write_log("释放结果集");
    }
 
    //获取最后插入的id
    public function insert_id() {
            $id = mysql_insert_id($this->link_id);
            $this->write_log("最后插入的id为".$id);
            return $id;
    }
 
    //关闭数据库连接
    protected function close() {
        $this->write_log("已关闭数据库连接");
        if (ConfigHelper::$MySQLType=="MSSQL2008"){
            if(is_resource($this->link_id)) 
               return sqlsrv_close($this->link_id);
        }
        else
            return @mysql_close($this->link_id);
    }
 
    //错误提示
    private function halt($msg='') {
        $msg .= "\r\n".mysql_error();
        $this->write_log($msg);
        die($msg);
    }
 
    //析构函数
    public function __destruct() {
        $this->free_result();
        $use_time = ($this-> microtime_float())-($this->time);
        $this->write_log("完成整个查询任务,所用时间为".$use_time);
        if($this->is_log){
            fclose($this->handle);
        }
    }
     
    //写入日志文件
    public function write_log($msg=''){
        if($this->is_log){
            $text = date("Y-m-d H:i:s")." ".$msg."\r\n";
            fwrite($this->handle,$text);
        }
    }
     
    //获取毫秒数
    public function microtime_float() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
}
 
?>