<?php
/**
 * @Author: anchen
 * @Date:   2017-07-31 15:55:59
 * @Last Modified by:   anchen
 * @Last Modified time: 2017-08-02 18:06:36
 */
require_once('./MySQLHelper.php');

class CaculateRooms
{
    public static function GetRooms_OneDay($hotelid,$dat)
    {
        $code=-1;
        $msg="";
        $db=new DB();
        $strsql="select RMS,OORMS,RMSAll from ntredatartp where Typ='房价类统计' and Dat='{$dat}' and TJ=1 and HotelID='{$hotelid}'";

        $arrtmp=$db->get_one($strsql);
        if ($arrtmp && is_array($arrtmp))
        {
            $code=1;
            $msg=$arrtmp;
            //获取开房数，房租收益
            $strsql="select isnull(sum(InRom),0) as InRom,isnull(sum(DRom),0) as DRom,isnull(sum(InRat),0) as InRat,isnull(sum(DRat),0) as DRat from ntredatartp where Typ='房价类统计' and Dat='{$dat}'  and HotelID='{$hotelid}'";
            $arr2=$db->get_one($strsql);
            $msg["InRom"]=$arr2["InRom"]; //过夜数
            $msg["DRom"]=$arr2["DRom"];   //过夜房租
            $msg["InRat"]=$arr2["InRat"]; //日租数
            $msg["DRat"]=$arr2["DRat"];   //日租房租

            $msg["ruzhushu"]=$msg["InRom"]+$msg["DRom"]/2;  //入住数=过夜数+日租数/2
            $msg["ruzhulv"]=$msg["ruzhushu"]/$msg["RMS"];    //入住率=入住数/可售房数
            $msg["pingjunfangjia"]=($msg["InRat"]+$msg["DRat"])/$msg["ruzhushu"]; //平均价=总房租/入住数
            $msg["PevPar"]=($msg["InRat"]+$msg["DRat"])/$msg["RMS"];    //平均房间收益=总房租收益/可出租房数
        }
        else
        {
            $code=-1;
            $msg="无数据";
        }
        return array("code"=>$code,"msg"=>$msg);
    }

    public static function GetRooms($hotelid,$datbegin,$datend)
    {
        $msg=array();
        $dt_start = strtotime($datbegin); 
        $dt_end   = strtotime($datend); 
        do { 
            $msg[date('Y-m-d',$dt_start)]=self::GetRooms_OneDay($hotelid,date('Y-m-d',$dt_start));
        } while (($dt_start += 86400) <= $dt_end);
        return array("code"=>1,"msg"=>$msg) ;
    }


    //根据操作员，查询他所管理的酒店在给定的时间段内的客房营业数据汇总
    //$Mob   登陆的操作员手机
    //$DayTyp 要查询的时间类型  Y-按年份查  M-按月份查 D-按日期查
    //$Dat 要查询的时间  传入一个日期，如果$DayTyp=Y,会自动取yyyy,=M时会自动取MM, 
    public static function GetRooms_ByOper($Mob,$DayTyp,$Dat)
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

$sqlselect ="select T1.*,T2.Rms,T2.OORMS,T2.RMSAll,(T1.InRom+T1.DRom/2) as ruzhushu,(case T2.Rms when 0 then 0 else (T1.InRom+T1.DRom/2)/T2.Rms end) as ruzhulv,case (InRom+DRom/2) when 0 then 0 else (T1.InRat+T1.DRat)/(InRom+DRom/2) end as pingjunfangjia,case T2.Rms when 0 then 0 else (T1.InRat+T1.DRat)/T2.Rms end as PevPar
from
(
select User_Hotel.UseMob,User_Hotel.HotelID,Hotels.HotelName,isnull(sum(InRom),0) as InRom,isnull(sum(DRom),0) as DRom,isnull(sum(InRat),0) as InRat,isnull(sum(DRat),0) as DRat
from User_Hotel
left join hotels on (User_Hotel.HotelID=Hotels.HotelID)
left join ntreDataRtp on (User_Hotel.HotelID=NtreDataRtp.HotelID and ntreDataRtp.Typ='房价类统计' and ntreDataRtp.Dat>='{$datbegin}' and ntreDataRtp.Dat<='{$datend}')
where User_Hotel.UseMob='{$Mob}' 
group by User_Hotel.UseMob,Hotels.HotelName,User_Hotel.HotelID
) as T1
left join 
(
select User_Hotel.UseMob,User_Hotel.HotelID,Hotels.HotelName,isnull(sum(RMS),0) as RMS,isnull(sum(OORMS),0) as OORMS,isnull(sum(RMSAll),0) as RMSAll
from User_Hotel
left join hotels on (User_Hotel.HotelID=Hotels.HotelID)
left join ntreDataRtp on (User_Hotel.HotelID=NtreDataRtp.HotelID and NtreDataRtp.Typ='房价类统计' and NtreDataRtp.Dat>='{$datbegin}' and NtreDataRtp.Dat<='{$datend}' and NtreDataRtp.TJ=1)
where User_Hotel.UseMob='{$Mob}'
group by User_Hotel.UseMob,Hotels.HotelName,User_Hotel.HotelID
) as T2
on (T1.HotelID=T2.HotelID) ";


            /*
            $sqlselect=' select T1.*,T2.RMS,T2.OORMS,T2.RMSALL ';
            $sqlselect.='from ';
            $sqlselect.='( ';
            $sqlselect.='select Hotels.HotelName,ntredatartp.HotelID,isnull(sum(InRom),0) as InRom,isnull(sum(DRom),0) as DRom,isnull(sum(InRat),0) as InRat,isnull(sum(DRat),0) as DRat ';
            $sqlselect.='from ntredatartp ' ;
            $sqlselect.='left join hotels on (ntredatartp.hotelid=hotels.hotelid) ';
            $sqlselect.="where Typ='房价类统计' and Dat>='{$datbegin}' and Dat<'{$datend}'  and ntredatartp.HotelID in ('{$hoteids}') ";
            $sqlselect.='group by ntredatartp.HotelID,Hotels.HotelName ';
            $sqlselect.=') as T1 ';
            $sqlselect.='left join ';
            $sqlselect.='( ';
            $sqlselect.='select Hotels.HotelName,ntredatartp.HotelID,isnull(sum(RMS),0) as RMS,isnull(sum(OORMS),0) as OORMS,isnull(sum(RMSAll),0) as RMSAll ';
            $sqlselect.='from ntredatartp ';
            $sqlselect.='left join hotels on (ntredatartp.hotelid=hotels.hotelid) ';
            $sqlselect.="where Typ='房价类统计' and Dat>='{$datbegin}' and Dat<'{$datend}' and TJ=1 and ntredatartp.HotelID in ('{$hoteids}') ";
            $sqlselect.='group by ntredatartp.HotelID,Hotels.HotelName ';
            $sqlselect.=') as T2 ';
            $sqlselect.='on (T1.HotelID=T2.HotelID) ';
            */
            $arrret["msg"]=$db->get_all($sqlselect);
            return $arrret;
        }
        else
        {
            return array("code"=>-1,"msg"=>"该用户下没有管理的酒店");
        }

    }

    //分析一天的客源情况(单个酒店)
    public static function KeYuanFenXi_OneDay($HotelID,$Dat)
    {
        $strsql="select Cod,InRom, DRom,InRat,DRat,(InRat+DRat) as YingYeE,(DRom/2+InRom) as KaiFangShu,case (InRom+DRom/2) when 0 then 0 else (InRat+DRat)/(InRom+DRom/2) end as PingJunZhu from ntredatartp 
            where Typ='房价类统计' and Dat='{$Dat}'  and HotelID='{$HotelID}'";

        $db=new DB();
        $arr=$db->get_all($strsql);
        return $arr;
    }

    //分析一段时间的客源情况(单个酒店)
    public static function KeYuanFenXi_Days($HotelID,$DayTyp,$Dat)
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

        $db=new DB();
        $strsql="select Cod,isnull(sum(InRom),0) as InRom, isnull(sum(DRom),0) as DRom,isnull(sum(InRat),0) as InRat,isnull(sum(DRat),0) as DRat,
            isnull(sum(InRom+DRom/2),0) as KaiFangShu,isnull(sum(InRat+DRat),0) as YingYeE,case(sum(InRom+DRom/2)) when 0 then 0 else sum(InRat+DRat)/sum(InRom+DRom/2) end as PingJuZu
            from ntredatartp 
            where Typ='房价类统计' and Dat>='{$datbegin}' and Dat<='{$datend}' and HotelID='{$HotelID}'
            group by Cod
            order by Cod";
        try
        {
            return array("code"=>1,"msg"=>$db->get_all($strsql));
        }
        catch (Exception $e)
        {
            return array("code"=>-1,"msg"=>$e->getMessage());
        }
    }

}

//$arr=CaculateRooms::KeYuanFenXi_Days('HotelXCX000001','D','2017-07-29');
//var_dump($arr);

?>