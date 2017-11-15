<?php


define(UPLOAD_ERR_OK, 0);
$fn = (isset($_SERVER['HTTP_X_FILENAME'])?$_SERVER['HTTP_X_FILENAME']:false);

if ($fn) //通过Ajax上传
{

    $filename = $fn;  
    echo json_encode(array("code"=>1,"msg"=>$_FILES));
    $files = $_FILES["fileList"];
    
    $tmpnam=time().'_'.$files["name"];
    if ($files["error"]==0)
    {
        //$name= dirname(dirname(__FILE__))."/imgupload/".$tmpnam; 
        $name="../imgupload/".$tmpnam;
        try{
            move_uploaded_file($files["tmp_name"],$name);
            //$nam="https://".$_SERVER['SERVER_NAME']."/".basename(dirname(dirname(dirname(dirname(__FILE__)))))."/".basename(dirname(dirname(dirname(__FILE__))))."/".basename(dirname(dirname(__FILE__)))."/imgupload/".$tmpnam; 
            $nam=$name;
            echo json_encode(array("code"=>1,"msg"=>$nam,"name"=>$files["name"]));
        }
        catch (Exception $ex){
            echo json_encode(array("code"=>0,"msg"=>"上传图片失败".$ex->getMessage()));
        }
    }
    else
    {
       echo json_encode(array("code"=>0,"msg"=>"上传图片失败".$files["error"])); 
    }
 }
else
{ 

    //通过表单上传

    if (!isset($_FILES['fileList'])) die("没有名为fileList的表单被提交");
    if ($_FILES['fileList']['error']==0)
    {
        $name='../imgupload/'.$_FILES['fileList']['name'];
        $tmpnam=$_FILES['fileList']["tmp_name"];
        if (move_uploaded_file($tmpnam,$name))
        {
            $retnam="https://".$_SERVER['SERVER_NAME']."/".basename(dirname(dirname(__FILE__)))."/imgupload/{$_FILES['fileList']['name']}";
            echo json_encode(array("code"=>1,"msg"=>$retnam));
        }
        else
        {
            echo json_encode(array("code"=>-1,"msg"=>'失败'));
        }
    }
    else
    {
        echo json_encode(array("code"=>-1,"msg"=>$_FILES['fileList']['error']));
    }
}

?>