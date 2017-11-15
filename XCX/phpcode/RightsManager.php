<?php
/**
 * @Author: anchen
 * @Date:   2017-07-21 15:41:11
 * @Last Modified by:   anchen
 * @Last Modified time: 2017-07-20 18:20:21
 */
require_once('./UserManager.php');
class RightsManagerClass
{
    //判断登陆用户是否有权限
    //$LoginMob 登陆用户手机号
    //权限号
    public static function CheckRights($LoginMob,$RightsCod)
    {

        if ($LoginMob=="" || is_null($LoginMob)) return false;
        if ($LoginMob=="SuperAdmin") return true;
        else
        {
            $U=new UserManagerClass($LoginMob);
            return in_array($RightsCod, $U->RightsDetail);
        }
    }
}
?>