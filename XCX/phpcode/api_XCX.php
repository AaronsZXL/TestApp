<?php
/**
 * @Author: anchen
 * @Date:   2017-07-04 14:29:03
 * @Last Modified by:   anchen
 * @Last Modified time: 2017-08-02 18:10:00
 */
require_once('./MySQLHelper.php');
$arr=array("code"=>-1,"msg"=>"");
if (isset($_POST["ActionType"]))
{
    $ActionType=$_POST["ActionType"];
    $arr=array();
    $hid=isset($_POST["hid"])?$_POST["hid"]:"";
    switch ($ActionType)
    {   
        case "CaculateNtcs":   //单店财务统计
            $LoginMob=isset($_POST["LoginMob"])?$_POST["LoginMob"]:"";
            include('./RightsManager.php');
            if (!RightsManagerClass::CheckRights($LoginMob,'206'))
            {
                $arr["code"]="-1";
                $arr["msg"]="您无此权限";
                break;
            }
            $datbeg=isset($_POST["datbegin"])?$_POST["datbegin"]:date('Y-m-d');
            $datend=isset($_POST["datend"])?$_POST["datend"]:date('Y-m-d');
            $hotelid=$_POST["hotelid"];
            include('./CaculateNtcs.php');
            $msg=CaculateNtcs::GetData_ByDays($hotelid,$datbeg,$datend);
            $arr=$msg;
            break;
        case "Get_Caculate_Room":           //单店营业统计
            $LoginMob=isset($_POST["LoginMob"])?$_POST["LoginMob"]:"";
            include('./RightsManager.php');
            if (!RightsManagerClass::CheckRights($LoginMob,'205'))
            {
                $arr["code"]="-1";
                $arr["msg"]="您无此权限";
                break;
            }
            $datbeg=isset($_POST["datbegin"])?$_POST["datbegin"]:date('Y-m-d');
            $datend=isset($_POST["datend"])?$_POST["datend"]:date('Y-m-d');
            $hotelid=$_POST["hotelid"];
            include('./CaculateRooms.php');
            $msg=CaculateRooms::GetRooms($hotelid,$datbeg,$datend);
            $arr=$msg;
            break;
        case "Caculate_Ntre_Total": //全部店营业统计 根据操作员，查询他所管理的酒店在给定的时间段内的客房营业数据汇总
            $Mob=$_POST["Mob"];     //操作员手机
            include('./RightsManager.php');
            if (!RightsManagerClass::CheckRights($Mob,'207'))
            {
                $arr["code"]="-1";
                $arr["msg"]="您无此权限";
                break;
            }
            $Typ=$_POST["Typ"];     //要查询的时间类型  Y-按年份查  M-按月份查 D-按日期查
            $Dat=$_POST["Dat"];  //传入一个日期,如果$DayTyp=Y,会自动取yyyy,=M时会自动取MM,否则计算这个日期的汇总
            include('./CaculateRooms.php');
            $msg=CaculateRooms::GetRooms_ByOper($Mob,$Typ,$Dat);
            $arr=$msg;
            break;
        case "Caculate_Ntcs_Total": //全店财务统计
            $Mob=$_POST["Mob"];     //操作员手机
            include('./RightsManager.php');
            if (!RightsManagerClass::CheckRights($Mob,'208'))
            {
                $arr["code"]="-1";
                $arr["msg"]="您无此权限";
                break;
            }
            $Typ=$_POST["Typ"];     //要查询的时间类型  Y-按年份查  M-按月份查 D-按日期查
            $Dat=$_POST["Dat"];
            include('./CaculateNtcs.php');
            $msg=CaculateNtcs::GetData_ByDays($Mob,$Typ,$Dat);
            $arr=$msg;
            break;   
        case "KeYuanFenXi":          //客分析
            $Mob=$_POST["Mob"];     //操作员手机
            include('./RightsManager.php');
            if (!RightsManagerClass::CheckRights($Mob,'209'))
            {
                $arr["code"]="-1";
                $arr["msg"]="您无此权限";
                break;
            }
            $Hotelid=$_POST["HotelID"];
            $Typ=$_POST["Typ"];     //要查询的时间类型  Y-按年份查  M-按月份查 D-按日期查
            $Dat=$_POST["Dat"];  //传入一个日期,如果$DayTyp=Y,会自动取yyyy,=M时会自动取MM,否则计算这个日期的汇总
            include('./CaculateRooms.php');
            $msg=CaculateRooms::KeYuanFenXi_Days($Hotelid,$Typ,$Dat);
            $arr=$msg;
            break; 
        case "GetRoom":   //获取房间
            $LoginMob=isset($_POST["LoginMob"])?$_POST["LoginMob"]:"";
            include('./RightsManager.php');
            if (!RightsManagerClass::CheckRights($LoginMob,'201'))
            {
                $arr["code"]="-1";
                $arr["msg"]="您无此权限";
                break;
            }

            $stat=isset($_POST["stat"])?$_POST["stat"]:"";
            $rtp=isset($_POST["rtp"])?$_POST["rtp"]:"";
            $fid=isset($_POST["floorid"])?$_POST["floorid"]:"";
            $sql="select Rno,Lno,RoomControlNO,Rnos.RTP,Rnos.BuildID,rnos.FloorID,RST,R00,Hotels.HotelName,builds.BuildNam,Floors.FloorNam,Stts.CNM  from Rnos left join Hotels on (rnos.HotelID=Hotels.HotelID) left join Builds on (rnos.HotelID=Builds.HotelID and rnos.BuildID=builds.BuildID) left join Floors on (rnos.HotelID=Floors.HotelID and Rnos.FloorID=Floors.FloorID)left join Stts on (rnos.RTP=stts.RTP and rnos.HotelID=stts.HotelID) ";
            $sql.=" where rnos.hotelid='".$hid."' ";
            if ($stat!="") $sql.=" and Rnos.RST='".$stat."'";
            if ($rtp!="") $sql.=" and Rnos.Rtp='".$rtp."'";
            if ($fid!="") $sql.=" and Rnos.FloorID='".$fid."'";
            try
            {
                $db=new DB();
                $arr["code"]=1;
                $arr["msg"]=$db->get_all($sql);
            }
            catch(Exception $e)
            {
                $arr["code"]=-100;
                $arr["msg"]=$e->getMessage();
            }
            break;
        case "GetRtps": //根据HotelID获取房型
            $sql="select * from Stts where HotelID='".$hid."'";
            try
            {
                $db=new DB();
                $arr["code"]=1;
                $arr["msg"]=$db->get_all($sql);
            }
            catch(Exception $e)
            {
                $arr["code"]=-100;
                $arr["msg"]=$e->getMessage();
            }
            break;
        case "GetFloors"://根据HotelID获取楼层
            $sql="select * from Floors where HotelID='".$hid."'";
            try
            {
                $db=new DB();
                $arr["code"]=1;
                $arr["msg"]=$db->get_all($sql);
            }
            catch(Exception $e)
            {
                $arr["code"]=-100;
                $arr["msg"]=$e->getMessage();
            }
            break;
        case "GetHotelNameinfo": //根据HotelID获取酒店名称
            $sql="select * from Hotels where HotelID='".$hid."'";
            try
            {
                $db=new DB();
                $arr["code"]=1;
                $arr["msg"]=$db->get_all($sql);
            }
            catch(Exception $e)
            {
                $arr["code"]=-100;
                $arr["msg"]=$e->getMessage();
            }
            break;    
        default:
            break;
    }
    echo json_encode($arr);
}


// $parms=$_GET["Parms"];

// $parmsarr=json_decode($parms);
// var_dump($parmsarr);
// $ActionType=$parmsarr["Parms"];
// echo $ActionTyp;
// switch ($ActionType)
// {   
//     case "GetRoom":
//         $hid=isset($parms["hid"])?$parms["hid"]:"";
//         $stat=isset($parms["stat"])?$parms["stat"]:"";
//         $sql="select Rno,Lno,RoomControlNO,RTP,Rnos.BuildID,rnos.FloorID,RST,R00,Hotels.HotelName,builds.BuildNam,Floors.FloorNam from Rnos left join Hotels on (rnos.HotelID=Hotels.HotelID) left join Builds on (rnos.HotelID=Builds.HotelID and rnos.BuildID=builds.BuildID) left join Floors on (rnos.HotelID=Floors.HotelID and Rnos.FloorID=Floors.FloorID) ";
//         $sql.=" where rnos.hotelid='".$hid."' ";
//         if ($stat!="") $sql.=" and Rnos.RST='".$stat."'";
//         try
//         {
//             $db=new DB();
//             $arr["code"]=1;
//             $arr["msg"]=$db->get_all($sql);
//         }
//         catch(Exception $e)
//         {
//             $arr["code"]=-100;
//             $arr["msg"]=$e->getMessage();
//         }
//         break;
//     default:
//         break;
// }
// echo json_encode($arr);



?>