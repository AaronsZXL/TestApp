<?php
//error_reporting(0);
//header("Content-type: text/html; charset=utf-8");
 class ConfigHelper
 {
    //echo "<script language=\"JavaScript\">alert(\"".$_SESSION['open_id']."\");</script>";
	/*数据库相关配置*/
     
    public static $MySQLType='MSSQL2008';// 'SAE'表示新浪SAE数据库，'MSSQL2008'-表示可通过用地址，端口，户名和密码访问的数据库
     
    //$MySQLType='SAE' 时，下面三项设置无效
    public static $hostname ="175.43.121.18"; //服务器地址 "localhost:3306";
    public static $username = "sa"; //数据库用户名
    public static $password = "Hwsoft888!@#"; //数据库密码
    public static $database = "XCX_Hotel"; //数据库名称
    public static $port=1433;               //数据库端口 
    public static $charset = "UTF-8";//数据库编码 sae->utf8 MSSQL2008->UTF-8   
    public static $pconnect = 1;// 0-即时连接,否则为持久连接
    public static $log = 0;//开启日志 $MySQLType='SAE'时，不能开启日志，因为SAE上不允许创建文件
    public static $logfilepath = 'Log';//日志的记录文件
	
	/*超级用户配置*/
	public static $sys_userName="admin";
	public static $sys_passWord="hwsoft888";
	
	/*微信配置*/

	public static $wx_appid="wxa005b2fb33de08f1"; //AppID(应用ID) 
	public static $wx_appsecret="ad477b102d1f943ecb312403fe3ea1a5"; //AppSecret(应用密钥)

 }
?>