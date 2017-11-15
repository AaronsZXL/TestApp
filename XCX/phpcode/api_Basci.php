<?php
/**
 * @Author: anchen
 * @Date:   2017-07-20 10:52:16
 * @Last Modified by:   anchen
 * @Last Modified time: 2017-07-21 15:00:52
 */
require_once('./UserManager.php');
$arr=array("code"=>-1,"msg"=>"");
if (isset($_POST["ActionType"]))
{
    $action=$_POST["ActionType"]; //Mob=XX&Pswd=XXXnam=XX
    switch ($action) {
         case 'RegUser':
            $u=new UserManagerClass();
            $u->Mob=isset($_POST["Mob"])?$_POST["Mob"]:"";
            $u->Pswd=isset($_POST["Pswd"])?$_POST["Pswd"]:"";
            $u->nam=isset($_POST["nam"])?$_POST["nam"]:"未填写";
            $u->stat=isset($_POST["stat"])?$_POST["stat"]:0;
            $u->Rights=isset($_POST["Rights"])?$_POST["Rights"]:"Client";
            $arr=$u->AddNewUser();
            break;
        case "UpdUser":
            $Mob=isset($_POST["Mob"])? addslashes($_POST["Mob"]):"";
            $u=new UserManagerClass($Mob);
            $arrparms=array();
            $arrkey=array();
            $arrval=array();
            if (isset($_POST["Pswd"])) {array_push($arrkey,"Pswd");array_push($arrval,$_POST["Pswd"]);}
            if (isset($_POST["nam"])) {array_push($arrkey,"nam");array_push($arrval,$_POST["nam"]);}
            if (isset($_POST["stat"])) {array_push($arrkey,"stat");array_push($arrval,$_POST["stat"]);}
            if (isset($_POST["Rights"])) {array_push($arrkey,"Rights");array_push($arrval,$_POST["Rights"]);}
            $arrparms=array_combine($arrkey,$arrval);
            $arr=$u->UpdateUser($arrparms);
            break;
        case "LoginUser":
            $Mob=isset($_POST["Mob"])? addslashes($_POST["Mob"]):"";
            $Pswd=isset($_POST["Pswd"])? addslashes($_POST["Pswd"]):"";
            $U=new UserManagerClass();
            $arr=$U->login($Mob,$Pswd);
            break;
        case "logoutUser":
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