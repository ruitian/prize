<?php session_start(); ini_set(”output_buffering”, “1″);?>
<meta charset="utf-8">

<?php   
error_reporting(E_ERROR | E_PARSE);
$appid = "appid";  
$secret = "secret";  
$code = $_GET["code"];  
$get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';  


$con = mysqli_connect("主机","用户名","密码")or die("数据库链接失败");
mysqli_set_charset ($con,utf8);


function https_request($url, $data = null) //url 请求函数
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

$output = https_request($get_token_url);
$json_obj = json_decode($output);
$array = get_object_vars($json_obj);
  
//根据openid和access_token查询用户信息  
$access_token = $array['access_token'];  
$openid = $array['openid'];  

$get_user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';  
$output = https_request($get_user_info_url);
$user_obj = json_decode($output);
$user_array = get_object_vars($user_obj); 

$get_user_token ="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
$output2= https_request($get_user_token);
$output2 = json_decode($output2);
$array2 = get_object_vars($output2);//转换成数组
$access_token2= $array2['access_token'];

$get_user_url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token2.'&openid='.$openid.'&lang=zh_CN';
$output3= https_request($get_user_url);
$output3 = json_decode($output3);
$array3 = get_object_vars($output3);//转换成数组
$subscribe= $array3['subscribe'];//输出subscribe 根据其值判断是否关注了公众号  

//解析json 

$_SESSION['user'] = $user_array;    

if($subscribe == 1){
?>  

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0,user-scalable=no">
	<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
     <?php 
        $nickname = $_SESSION['user']['nickname'];
        $openid = $_SESSION['user']['openid'];
        mysqli_select_db($con,"数据库名");
        $sql="select * from wx_movie where openid = '$openid'";
        $query = mysqli_query($con,$sql); 
        $row = mysqli_fetch_array($query);  
        $prize_name = $row['prize'];     
     ?>
     <div class="container-fluid">
        <div class="containers">
            <div class="top"><img src="img/top.jpg" alt=""></div>
            <div class="center">        
                <div class="left"><img src="img/left.jpg" alt=""></div>
                <div class="button">
                    <?php if(empty($row)) {?>
                        <?php if(empty($openid)) {?>
                        <img src="img/thank.jpg" alt="">
                        <?php } else { ?>
                        <a href="data.php"><img src="img/button.jpg" alt=""></a>
                        <?php } ?>
                    <?php } else {?>
                        <?php if($row['prize_id'] == '6' || $row['prize_id'] == '0' || $row['whether'] == '1') { ?>
                        <a href="data.php"><img src="img/thank.jpg" alt=""></a>
                        <?php } else { ?>
                        <div class="prizes" style="width:100%; background-color:white; border-radius:10px;">
                            <h3 style="color:grey;"><?php echo $prize_name;?></h3>
                        </div>
                        <?php } ?>
                    <?php } ?>
                </div>
                <div class="right"><img src="img/right.jpg" alt=""></div>
            </div>
            <div class="footer" style="margin-top:0px; padding-top:0px;">
                <img src="img/footer.jpg" alt="">
            </div>
        </div>
    </div>
</body>
</html>
<?php } else { 
     
$url = "http://mp.weixin.qq.com/s?__biz=MjM5NjM5NjkwMA==&mid=210685543&idx=1&sn=a347c9838168f4a5c022d7eed339b37b#wechat_redirect";  
echo "<script language='javascript' type='text/javascript'>";  
echo "window.location.href='$url'";  
echo "</script>";
}
?>
