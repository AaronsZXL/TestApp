<?php
/**
 * @Author: Zhouxl
 * @Date:   2017-07-20 10:05:15
 * @Last Modified by:   anchen
 * @Last Modified time: 2017-07-28 15:07:41
 */
require_once('./MySQLHelper.php');
class UserManagerClass
{
    public $Mob;
    public $Pswd;
    public $nam;
    public $stat;   //0-仅注册  1-已审核 -1已停用
    public $Rights; //SuperAdmin-超级管理员,Admin-管理员,Investors-投资方,Manager-管理者,Finance-酒店财务,Client-用户 
    public $RightsDetail;
    //构造函数
    public function __construct($Mobile="")
    {
        if ($Mobile=="")
        {
            $this->Mob="";
            $this->Pswd="";
            $this->nam="";
            $this->stat=-1;
            $this->Rights="";
            $this->RightsDetail=array();
        }
        else
        {
            $db=new DB();
            $sql="select Mob,Pswd,nam,stat,Rights,User_Rigths.RightsDetail from Users left join User_Rigths on (Users.Rights=User_Rigths.RightsNam) where Mob='".$Mobile."'";
            $result= $db->query($sql);
            $recordcount=$db->num_rows($result);
            if ($recordcount==1)
            {
                $arr=$db->get_one_record($result);
                $this->Mob=$arr["Mob"];
                $this->Pswd=$arr["Pswd"];
                $this->nam=$arr["nam"];
                $this->stat=$arr["stat"];
                $this->Rights=$arr["Rights"];
                $this->RightsDetail=explode(',',$arr["RightsDetail"]); 
            }
            else
            {
                $this->Mob="";
                $this->Pswd="";
                $this->nam="";
                $this->stat=-1;
                $this->Rights="";
                $this->RightsDetail=array();
            }
        }
    }

    //判断给定的手机号是否存在
    public  function isUserExists($Mobile)
    {
        if (!$Mobile) return false;
        $db=new DB();
        $result= $db->query("select Mob from users where Mob='".$Mobile."'");
        $recordcount=$db->num_rows($result);
        if ($recordcount>0) 
            return true;
        else 
            return false;
    }

    //添加新用户
    public function AddNewUser()
    {
        $errid=0;
        $errmsg="";
        if ($this->Mob)
        {
            if ($this->isUserExists($this->Mob))
            {
                $errid=-1;
                $errmsg="注册用户失败：".$this->Mob."手机已被注册";
            }
            else
            {
                try 
                {
                    $db=new DB();
                    $ret= $db->insert("Users", array(
                    "Mob"=>$this->Mob,"Pswd"=>$this->Pswd,"nam"=>$this->nam,"Rights"=>$this->Rights,"stat"=>$this->stat
                    ));
                    if (!$ret) 
                    {
                        $errid= -3;
                        $errmsg="注册用失败";
                    }
                    else
                    {
                        $errid= 1;
                        $errmsg="添加用户成功";
                    }
                }
                catch (Exception $e)
                {
                    $errid= -4;
                    $errmsg="注册用户异常：".$e->getMessage();
                }
            }
        }
        else
        {
                $errid=-2;
                $errmsg="用户手机号不能为空";
        }
        return array("code"=>$errid,"msg"=>$errmsg);
    }

    //更新用户信息 
    public function UpdateUser($arr)
    {
        $errid=0;
        $errmsg="";
        if ($arr)
        {
            try
            {
                $db=new DB();
                $ret=$db->update("users",$arr,"Mob='".$this->Mob."'");
                if (!$ret) 
                {
                    $errid= -3;
                    $errmsg="更新用户信息失败";
                }
                else
                {
                    $errid= 1;
                    $errmsg="更新用户信息成功";
                }
            }
            catch (Exception $e) 
            {
                $errid= -4;
                $errmsg="更新用户信息异常：".$e->getMessage();
            }
        }
        else
        {
            $errid= -1;
            $errmsg="更新用户信息失败：没有指定要更改的信息";
        }
        return array("code"=>$errid,"msg"=>$errmsg);
    }

    function Login($Mob,$Pswd)
    {
        $errid=-1;
        $errmsg="";

        if ($Mob==ConfigHelper::$sys_userName && $Pswd==ConfigHelper::$sys_passWord)
        {
            $U=new UserManagerClass();
            $U->Mob="SuperAdmin";
            $U->Pswd="";
            $U->nam="系统管理员";
            $U->stat=1;   //0-仅注册  1-已审核 -1已停用
            $U->Rights="SuperAdmin";
            return array("code"=>1,"msg"=>$U);
        }

        if (is_null($Mob) || ($Mob=='') || is_null($Pswd) || ($Pswd==''))
        {
            $errmsg="登陆名和密码不能为空";
        }
        else
        {
            $U=new UserManagerClass($Mob);
            if ($U->Mob=="")
            {
                $errmsg="用户不存在";
            }
            else
            {
                if ($Pswd !=$U->Pswd)
                {
                    $errmsg="登陆密码错误" ;
                }
                else if ($U->stat==-1)
                {
                    $errmsg="用户已停用";
                }
                else
                {
                    $errid=1;
                    $errmsg=$U;
                }
            }
        }
        return array("code"=>$errid,"msg"=>$errmsg);
    }
}
?>