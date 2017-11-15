<?php
/**
 * @Author: anchen
 * @Date:   2017-07-20 18:58:59
 * @Last Modified by:   anchen
 * @Last Modified time: 2017-07-20 14:50:33
 */
require_once('./config.php');
require_once('./Redis.class.php');

/*  redis 写入和读取数组
    $result=array("openid"=>"openid","token_time"=>time(),"session_key"=>"session_key");
    $openid="openid";
    RedisClass::SetOpenIDToRedis($openid,$result);
    echo (RedisClass::GetOpenIDFromRedis($openid));
    exit();
*/

$code=$_POST["code"];
$sessionid=$_POST["sessionid"];
$openid=RedisClass::GetOpenIDFromRedis($sessionid);
if ($openid)
{
    echo json_encode(array("code"=>1,"msg"=>$openid,"Source"=>"OLD"));
    exit();
}

$Url="https://api.weixin.qq.com/sns/jscode2session?appid=".ConfigHelper::$wx_appid."&secret=".ConfigHelper::$wx_appsecret."&js_code=".$code."&grant_type=authorization_code";
$res = file_get_contents($Url); 
$result = json_decode($res);
if (isset($result->openid))
{
    $arr= array(
        'token_time' => time(),
        'openid'=>$result->openid,
        'session_key'=>$result->session_key
     );
    RedisClass::SetOpenIDToRedis($result->openid,$arr);
    echo json_encode(array("code"=>1,"msg"=>$result->openid));
}
else
{

    echo json_encode(array("code"=>0,"msg"=>$result->errcode.":".$result->errmsg));
}
?>