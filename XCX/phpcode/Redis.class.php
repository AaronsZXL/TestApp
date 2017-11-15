<?php
/**
 * @Author: anchen
 * @Date:   2017-07-20 11:58:16
 * @Last Modified by:   anchen
 * @Last Modified time: 2017-07-20 14:49:37
 */

class RedisClass 
{
    
    public static function GetRedis($key)
    {
        $redis = new redis(); 
        if ($redis->connect('127.0.0.1', 6379))
        {
            if ($redis->exists($key))
                return $redis->get($key);
            else 
                return false; 
        }
        return false;
    }

    public static function SetRedis($Key, $value)
    {
        $redis = new redis(); 
        if ($redis->connect('127.0.0.1', 6379))
        {
            $result = $redis->set($key,$value);
            return $result;
        }
        else
            return false;
    }

    public static function DelRedis($key)
    {
        if ($redis->connect('127.0.0.1', 6379))
            $redis->delete($key); 
    }

    public static function SetOpenIDToRedis($SessionID,$arr)
    {
        $redis = new redis();
        if ($redis->connect('127.0.0.1', 6379))
        {
            $redis->hmset($SessionID,$arr);
        }
        else 
            return false;
    }

    public static function GetOpenIDFromRedis($SessionID)
    {
        $redis = new redis();
        $result=false;
        if ($redis->connect('127.0.0.1', 6379))
        {
            $result=$redis->hgetall($SessionID);
        }
        if ($result)
        {
            if (isset($result["token_time"]) && isset($result["openid"]) && isset($result["session_key"]))
            {
                $token_time=$result["token_time"];
                $openid = $result["openid"];
                $session_key= $result["session_key"];

                if($openid && $session_key && $token_time>time()-7200)
                    return $openid;
                else
                    return false;
            }
            else
            {
                return false;
            }
        }
        else
            return $result;
    }

    public static function DelOpenIDFromRedis($SessionID)
    {
        Self::DelRedis($SessionID);
    }
}
?>