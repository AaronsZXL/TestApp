<?php
/**
 * @Author: anchen
 * @Date:   2017-07-20 14:32:42
 * @Last Modified by:   anchen
 * @Last Modified time: 2017-07-28 16:27:50
 */
header('Access-Control-Allow-Origin:*');
require_once ('./HotelManager.php');
$arr=array("code"=>-1,"msg"=>"");
if (isset($_POST))
{
    // $data=$_POST;
    // $params=json_encode($data);
    // echo $params;
    // exit();
    $params=$_POST;
    if (isset($params["Action"]))
    {
        $action=$params["Action"];
        switch ($action) 
        {
            case 'User_Hotel_Get': //获取用户管理的酒店
                $Mob=isset($params["Mob"])?$params["Mob"]:"";
                if ($Mob=="")
                {
                    $arr["msg"]="用户为空";
                }
                else if ($Mob=="SuperAdmin") //超用用户
                {
                    $sql="select User_Hotel.HotelID,Hotels.HotelName from User_Hotel left join Hotels on (User_Hotel.HotelID=Hotels.HotelID)  order by hotels.HotelName";
                    $db=new DB();
                    $datas=$db->get_all($sql);
                    $arr["code"]=1;
                    $arr["msg"]=$datas;
                }
                else 
                {
                    $sql="select User_Hotel.HotelID,Hotels.HotelName from User_Hotel left join Hotels on (User_Hotel.HotelID=Hotels.HotelID) where User_Hotel.UseMob='".$Mob."' order by hotels.HotelName";
                    $db=new DB();
                    $datas=$db->get_all($sql);
                    $arr["code"]=1;
                    $arr["msg"]=$datas;
                }
                break;
            case "User_Hotel_Link":
                $Mob=isset($params["Mob"])?$params["Mob"]:"";
                $HotelID=isset($params["HotelID"])?$params["HotelID"]:"";
                $Operate=isset($params["Operate"])?$params["Operate"]:"";
                $LoginMob=isset($params["LoginMob"])?$params["LoginMob"]:"";
                include('./RightsManager.php');
                if (!RightsManagerClass::CheckRights($LoginMob,'106'))
                {
                    $arr["code"]="-1";
                    $arr["msg"]="您无此权限";
                    break;
                }
                if ($Mob=="")
                {
                    $arr["msg"]="未选择需要分配酒店的用户";
                }
                else if ($HotelID=="")
                {
                    $arr["msg"]="未选择需要分配的酒店";
                }
                else if ($Operate=="Add")
                {
                    $db=new DB();
                    $sql="select ID from User_Hotel where UseMob='{$Mob}' and HotelID='{$HotelID}'";
                    $r=$db->getsingle($sql);
                    if ($r)
                    {
                        $arr["code"]=-1;
                        $arr["msg"]="已经关联过，无需再次关联";
                    }
                    else
                    {
                        $ret=$db->insert("User_Hotel",array("UseMob"=>$Mob,"HotelID"=>$HotelID));
                        if ($ret)
                        {
                            $arr["code"]=1;
                            $arr["msg"]="操作成功";
                        }
                        else
                        {
                            $arr["code"]=-1;
                            $arr["msg"]="操作失败";
                        }
                    }
                }
                else if ($Operate=="Dec")
                {
                    $db=new DB();
                    $ret=$db->delete("User_Hotel","UseMob='".$Mob."' and HotelID='".$HotelID."'");
                    if ($ret)
                    {
                        $arr["code"]=1;
                        $arr["msg"]="操作成功";
                    }
                    else
                    {
                        $arr["code"]=-1;
                        $arr["msg"]="操作失败";
                    }
                }
                else
                {
                    $arr["msg"]="未选择要做的操作";
                }
                break;
            case 'AddHotel':
                $HotelName=isset($params["HotelName"])?$params["HotelName"]:"酒店未命名";
                $Description=isset($params["Description"])?$params["Description"]:"无酒店描述";
                $Address=isset($params["Address"])?$params["Address"]:"未设置地址";
                $Telephone=isset($params["Telephone"])?$params["Telephone"]:"";
                $HotelPic=isset($params["HotelPic"])?$params["HotelPic"]:"";
                $LinkMan=isset($params["LinkMan"])?$params["LinkMan"]:"无联系人";
                $LinkManMobile=isset($params["LinkManMobile"])?$params["LinkManMobile"]:"";
                $longitude=isset($params["longitude"])?$params["longitude"]:0;
                $latitude=isset($params["latitude"])?$params["latitude"]:0;
                $LoginMob=isset($params["LoginMob"])?$params["LoginMob"]:"";
                include('./RightsManager.php');
                if (RightsManagerClass::CheckRights($LoginMob,'105'))
                    $arr=HotelManagerClass::AddHotel($HotelName,$Description,$Address,$Telephone,$HotelPic,$LinkMan,$LinkManMobile,$longitude,$latitude);
                else
                {
                    $arr["code"]="-1";
                    $arr["msg"]="您无此权限";
                }
                break;

            case 'UpdHotel':
                $LoginMob=isset($params["LoginMob"])?$params["LoginMob"]:"";
                include('./RightsManager.php');
                if (!RightsManagerClass::CheckRights($LoginMob,'106'))
                {
                    $arr["code"]="-1";
                    $arr["msg"]="您无此权限";
                    break;
                }
                if (!isset($params["HotelID"]))
                {
                    $arr["msg"]="未指定要更改的信息";
                }
                else
                {

                    $HotelID=$params["HotelID"];
                    $arrparms=array();
                    $arrkey=array();
                    $arrval=array();
                    if (isset($params["Telephone"])) {array_push($arrkey,"Telephone");array_push($arrval,$params["AddTelephoneress"]);}
                    if (isset($params["HotelPic"])) {array_push($arrkey,"HotelPic");array_push($arrval,$params["HotelPic"]);}
                    if (isset($params["LinkMan"])) {array_push($arrkey,"LinkMan");array_push($arrval,$params["LinkMan"]);}
                    if (isset($params["LinkManMobile"])) {array_push($arrkey,"LinkManMobile");array_push($arrval,$params["LinkManMobile"]);}
                    if (isset($params["longitude"])) {array_push($arrkey,"longitude");array_push($arrval,$params["longitude"]);}
                    if (isset($params["latitude"])) {array_push($arrkey,"latitude");array_push($arrval,$params["latitude"]);}
                    if (isset($params["Address"])) {array_push($arrkey,"Address");array_push($arrval,$params["Address"]);}
                    if (isset($params["HotelName"])) {array_push($arrkey,"HotelName");array_push($arrval,$params["HotelName"]);}
                    if (isset($params["Description"])) {array_push($arrkey,"Description");array_push($arrval,$params["Description"]);}
                    $arrparms=array_combine($arrkey,$arrval);
                    $arr=HotelManagerClass::EdtHotel($HotelID,$arrparms);
                }
                break;
            case "DelHotel":
                break;
            case 'GetHotel':
                $HotelName=isset($params["HotelName"])?$params["HotelName"]:"";
                // $Description=isset($params["Description"])?$params["Description"]:"无酒店描述";
                // $Address=isset($params["Address"])?$params["Address"]:"未设置地址";
                // $Telephone=isset($params["Telephone"])?$params["Telephone"]:"";
                // $HotelPic=isset($params["HotelPic"])?$params["HotelPic"]:"";
                // $LinkMan=isset($params["LinkMan"])?$params["LinkMan"]:"无联系人";
                // $LinkManMobile=isset($params["LinkManMobile"])?$params["LinkManMobile"]:"";
                // $longitude=isset($params["longitude"])?$params["longitude"]:0;
                // $latitude=isset($params["latitude"])?$params["latitude"]:0;
                // $LoginMob=isset($params["LoginMob"])?$params["LoginMob"]:"";
                $db = new DB();
                $sql="select HotelID,HotelName,Description,Address,Telephone,HotelPic,LinkMan,LinkManMobile,Longitude,Latitude from Hotels where HotelName like'".$HotelName."%'";
                $datas = $db->get_all($sql);
                $arr['code']=1;
                $arr['msg']=$datas;
                break;
            default:
                break;
        }
    }
    else
    {
        $arr["msg"]="未指定要做的操作";
    }
}
echo json_encode($arr);
?>