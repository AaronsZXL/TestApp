<?php
/**
 * @Author: anchen
 * @Date:   2017-07-20 10:52:16
 * @Last Modified by:   anchen
 * @Last Modified time: 2017-07-28 15:06:53
 */
require_once('./UserManager.php');
$arr=array("code"=>-1,"msg"=>"");
if (isset($_POST["ActionType"]))
{
    $action=$_POST["ActionType"]; 
    switch ($action) {
         case "getUser":
             $Mob=isset($_POST["Mob"])? addslashes($_POST["Mob"]):"";
             if ($Mob!="")
             {
                 $u=new UserManagerClass();
                 $arr["code"]=1;
                 $arr["msg"]=$u;
            }
            else
            {
                $arr["msg"]="未指定用户";
            }
            break;
         case 'RegUser':                //注册用户
            $u=new UserManagerClass();
            $u->Mob=isset($_POST["Mob"])?$_POST["Mob"]:"";
            $u->Pswd=isset($_POST["Pswd"])?$_POST["Pswd"]:"";
            $u->nam=isset($_POST["nam"])?$_POST["nam"]:"未填写";
            $u->stat=isset($_POST["stat"])?$_POST["stat"]:1;
            $u->Rights=isset($_POST["Rights"])?$_POST["Rights"]:"Client";
            $arr=$u->AddNewUser();
            break;
        case "UpdUser":                 //修改用户资料
            $Mob=isset($_POST["Mob"])? addslashes($_POST["Mob"]):"";
            $u=new UserManagerClass($Mob);
            $arrparms=array();
            $arrkey=array();
            $arrval=array();
            if (isset($_POST["Pswd"])) {array_push($arrkey,"Pswd");array_push($arrval,$_POST["Pswd"]);}
            if (isset($_POST["nam"])) {array_push($arrkey,"nam");array_push($arrval,$_POST["nam"]);}
            $arrparms=array_combine($arrkey,$arrval);
            $arr=$u->UpdateUser($arrparms);
            break;
        case "UserStat_Change":             //更改用户的启用/停用状态
            $Mob=isset($_POST["Mob"])? addslashes($_POST["Mob"]):"";
            $stat=isset($_POST["stat"])?$_POST["stat"]:-1;
            $LoginMob=$_POST["LoginMob"];
            include('./RightsManager.php');
            if (RightsManagerClass::CheckRights($LoginMob,'101'))
            {
                $db=new DB();
                $db->query("Update Users set Stat={$stat} where Mob='{$Mob}'");
                $arr["code"]=1;
                $arr["msg"]="操作成功";
            }
            else
            {
                $arr["code"]=-1;
                $arr["msg"]="您无此权限";
            }
            break;
        case "GetUserInfo": //获取用户信息
            $Mob=isset($_POST["Mob"])?$_POST["Mob"]:"";
            $Hid=isset($_POST["HotelID"])?$_POST["HotelID"]:"";
            $sql="";
            if ($Mob!="")
            {
                $sql="select * from users where Mob='{$Mob}'";
            }
            else if ($Hid!="")
            {
                $sql="select * from Users where Mob in (select usemob from User_Hotel where HotelID='{$Hid}')";
            }
            $db=new DB();
            $arr["code"]=1;
            $arr["msg"]=$db->get_all($sql);
            break;
        case "UserRights_Change":   //更改用户权限级别
            $LoginMob=$_POST["LoginMob"];
            $Mob=$_POST["Mob"];
            $Rights=$_POST["Rights"];
            include('./RightsManager.php');
            if (RightsManagerClass::CheckRights($LoginMob,'104'))
            {
                $db=new DB();
                $db->query("Update Users set Rights='{$Rights}' where Mob='{$Mob}'");
                $arr["code"]=1;
                $arr["msg"]="操作成功";
            }
            else
            {
                $arr["code"]=-1;
                $arr["msg"]="您无此权限";
            }
            break;
        case "LoginUser":               //用户登陆
            $Mob=isset($_POST["Mob"])? addslashes($_POST["Mob"]):"";
            $Pswd=isset($_POST["Pswd"])? addslashes($_POST["Pswd"]):"";
            $U=new UserManagerClass();
            $arr=$U->login($Mob,$Pswd);
            break;
        case "logoutUser":              //用户注消
            $arr["code"]=1;
            $arr["msg"]="注消成功";
            break;
        default:
            $arr["msg"]="操作类型不正确";
            break;
    } 
}
else
{
    $arr["msg"]="未指定要做的操作";
}
echo json_encode($arr);
?>