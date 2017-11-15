<?php
/**
 * @Author: anchen
 * @Date:   2017-07-20 13:57:02
 * @Last Modified by:   anchen
 * @Last Modified time: 2017-07-28 16:29:39
 */
require_once('./MySQLHelper.php');
require_once("./guid.class.php"); 
class HotelManagerClass
{
    public static function EdtHotel($HotelID,$arrdata)
    {
        $errid=0;
        $errmsg="";

        if ($arrdata)
        {
            try
            {
                $db=new DB();
                $ret=$db->update("hotels",$arrdata,"HotelID='".$HotelID."'");
                if (!$ret) 
                {
                    $errid= -3;
                    $errmsg="更新酒店信息失败";
                }
                else
                {
                    $errid= 1;
                    $errmsg="更新酒店信息成功";
                }
            }
            catch (Exception $e) 
            {
                $errid= -4;
                $errmsg="更新酒店信息异常：".$e->getMessage();
            }
        }
        else
        {
            $errid= -1;
            $errmsg="更新酒店信息失败：没有指定要更改的信息";
        }
        return array("code"=>$errid,"msg"=>$errmsg);
    }

    public static function AddHotel($HotelName,$Description,$Address,$Telephone,$HotelPic,$LinkMan,$LinkManMobile,$longitude,$latitude)
    {
        $arrret=array('code' =>-1 ,'msg'=>"其他错误");

        $Guid = new Guid();    
        $hotelid=$Guid->toString();
        $arr=array(
            "HotelID"=>$hotelid,
            "HotelName"=>$HotelName,
            "Description"=>$Description,
            "Address"=>$Address,
            "Telephone"=>$Telephone,
            "HotelPic"=>$HotelPic,
            "LinkMan"=>$LinkMan,
            "LinkManMobile"=>$LinkManMobile,
            "longitude"=>$longitude,
            "latitude"=>$latitude,
            "RegisterMobile"=>$U->Mob
            );
        $db=new DB();
        
        try
        {
            $ret= $db->insert("hotels",$arr);
            if ($ret)
            {
                $arrret["code"]=1;
                $arrret["msg"]="注册酒店成功";
            }
            else
            {
                $arrret["msg"]="注册酒店失败";
            }
        }
        catch(Exception $e)
        {
            $arrret["msg"]=$e->getMessage();
        }
        return $arrret;
    }
}

?>