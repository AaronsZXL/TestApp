<?php
/**
 * @Author: anchen
 * @Date:   2017-08-01 16:02:49
 * @Last Modified by:   anchen
 * @Last Modified time: 2017-08-02 16:43:20
 */

require_once('./MySQLHelper.php');
class CaculateNtcs
{
    public static function GetData_OneDay($hotelid,$datbegin)
    {
        $sqlstr="select isnull(SUM(DAmt),0) as DAmt,isnull(SUM(CAmt),0) as CAmt From NtcsData where Dat = '{$datbegin}' and HotelID='{$hotelid}'";

        $db=new DB();

        return $db->get_one($sqlstr);

    }

    public static function GetData_ByDays($hotelid,$datbegin,$datend)
    {
        $msg=array();
        $dt_start = strtotime($datbegin); 
        $dt_end   = strtotime($datend); 
        do { 
            $msg[date('Y-m-d',$dt_start)]=self::GetData_OneDay($hotelid,date('Y-m-d',$dt_start));
        } while (($dt_start += 86400) <= $dt_end);
        return array("code"=>1,"msg"=>$msg);
    }

    public static function GetDatas_ByOper($Mob,$Typ,$Dat)
    {
        $datbegin=$Dat;
        $datend=$Dat;
        $Datparms=strtotime($Dat);
        $y=date('Y',$Datparms);
        $m=date('m',$Datparms);
        $d=date('d',$Datparms);

        //获取时间
        switch ($DayTyp)
        {
            case "Y":
                $datbegin=$y.'-01-01';
                $datend=$Dat;
                //$datend=date('Y-m-d',strtotime("+1 year",strtotime($datbegin)));
                break;
            case "M":
                $datbegin=$y.'-'.$m.'-01';
                $datend=$Dat;
                //$datend=date('Y-m-d',strtotime("+1 month",strtotime($datbegin)));
                break;
            case "D":
                $datbegin=$Dat;
                $datend=$Dat;
                //$datend=date('Y-m-d',strtotime("+1 day",strtotime($datbegin)));
                break;
            default:
                break;
        }

        //获取酒店
        $db=new DB();
        $sqlstr="select HotelID from  User_Hotel where UseMob='{$Mob}'";
        $arrhotels=$db->get_all($sqlstr);
        if ($arrhotels && is_array($arrhotels) && count($arrhotels)>=1)
        {
            $arrret=array();
            $arrret["code"]=1;
            $sql="select UseMob,User_hotel.HotelID,Hotels.HotelName,isnull(SUM(DAmt),0) as DAmt,isnull(SUM(CAmt),0) as CAmt from user_hotel
                left join Hotels on (User_hotel.HotelID=Hotels.HotelID)
                left join ntcsdata on (User_Hotel.HotelID=NtcsData.HotelID and Dat >= '{$datbegin}' and Dat<='{$datend}')
                where UseMob='{$Mob}'
                group by UseMob,User_hotel.HotelID,Hotels.HotelName";
            $arrret["msg"]=$db->get_all($sql);
            return $arrret;
        }
        else
        {
            return array("code"=>-1,"msg"=>"该用户下没有管理的酒店");
        }
    }
}

//$arr=CaculateNtcs::GetData_ByDays('HotelXCX000001','2017-07-25','2017-07-29');
//$arr=CaculateNtcs::GetDatas_ByOper('18775242783','Y','2017-07-29');
//echo json_encode($arr);
?>