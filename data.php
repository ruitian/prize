<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title></title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,user-scalable=no">
    <style>
        .container-fluid {
            width: 100%;
            background-color: #fcf5cd;
        }
        .container img {
            width: 100%;
        }
        .content {
            margin: 0 auto;
            width: 50%;
            height: 200px;
            background-color: white;
            margin-top: 5%;
            border-radius: 10px;
        }

        .content-footer {
            margin: 0 auto;
            width: 100%;
            text-align: center;
        }
        .content-footer h2 {
            padding-top: 30px;
        }
    </style>
</head>
<body>

<?php
/*
* array unique_rand( int $min, int $max, int $num )
* 生成一定数量的不重复随机数
* $min 和 $max: 指定随机数的范围
* $num: 指定生成数量
*/
function unique_rand($min, $max, $num) {
  $count = 0;
  $return = array();
  while ($count < $num) {
      $return[] = mt_rand($min, $max);
      $return = array_flip(array_flip($return));
      $count = count($return);
  }
  shuffle($return);
  return $return;
}
$arr = unique_rand(1, 100, 6);
sort($arr);
$result_rand = '';
for($i=0; $i < count($arr);$i++)
{
  $result_rand .= $arr[$i];
}
$result_rand = substr($result_rand, 0, -1);

?> 

<?php 
error_reporting(E_ALL ^ E_NOTICE);//防止php出现未定义等错误
$con = mysqli_connect("主机","用户名","密码")or die("数据库链接失败");
mysqli_set_charset ($con,utf8);


function get_rand($proArr) { 
    $result = ''; 
 
    //概率数组的总概率精度 
    $proSum = array_sum($proArr); 
 
    //概率数组循环 
    foreach ($proArr as $key => $proCur) { 
        $randNum = mt_rand(1, $proSum); 
        if ($randNum <= $proCur) { 
            $result = $key; 
            break; 
        } else { 
            $proSum -= $proCur; 
        } 
    } 
    unset ($proArr); 
    return $result; 
} 

mysqli_select_db($con,"数据库名");
    
$prize_arr = array( 
    '0' => array('id'=>1,'prize'=>'明星签名书包','v'=>1), 
    '1' => array('id'=>2,'prize'=>'电影人物手办','v'=>5), 
    '2' => array('id'=>3,'prize'=>'电影票一张','v'=>10), 
    '3' => array('id'=>4,'prize'=>'这不是奖品','v'=>0), 
    '4' => array('id'=>5,'prize'=>'这不是奖品','v'=>0), 
    '5' => array('id'=>6,'prize'=>'下次没准就能中哦','v'=>984), 
); 

foreach ($prize_arr as $key => $val) { 
    $arr[$val['id']] = $val['v']; 
}
 
$rid = get_rand($arr); //根据概率获取奖项id 

$res['yes'] = $prize_arr[$rid-1]['prize']; //中奖项 
$res['id'] = $prize_arr[$rid-1]['id'];

unset($prize_arr[$rid-1]); //将中奖项从数组中剔除，剩下未中奖项 
shuffle($prize_arr); //打乱数组顺序 
for($i=0;$i<count($prize_arr);$i++){ 
    $pr[] = $prize_arr[$i]['prize']; 
} 

$res['no'] = $pr; 
$prize_name =  $res['yes'];
$prize_id = $res['id'];
// echo json_encode($res['yes']); 
// echo $prize_name;
// echo $prize_id;
// echo $prize_name;
mysqli_select_db($con,"数据库名");
$openid = $_SESSION['user']['openid'];
$nickname = $_SESSION['user']['nickname'];
$sql_prize = "select * from wx_prize where id='$prize_id'";
$sql_openid = "select * from wx_movie where openid='$openid'";
$query_openid = mysqli_query($con, $sql_openid);
$row_openid = mysqli_fetch_array($query_openid);

$query = mysqli_query($con, $sql_prize);
$prize_content = mysqli_fetch_array($query);

if(empty($row_openid)) {
    if($prize_id=='6'||$prize_id=='4'||$prize_id=='5'||$prize_id=='0'||empty($prize_id))
        {
    ?>
    <div class="container-fluid">
        <div class="container">
            <img src="img/false.jpg" alt="">
        </div>
    </div>
    <?php } else { ?>
        <?php if ($prize_content['count'] > 0) {?>
    <div class="container-fluid">
        <div class="container">
            <div class="top">
                <img src="img/top_prize.jpg" alt="">
            </div>
            <div class="content">
                <div class="content-footer">
                    <h2 style="color:grey;"><?php echo $prize_name;?></h2>
                    <?php 
                        $ii = 1;
                        $prize_count = $prize_content['count'] - $ii; 
                        $sql_count = "update wx_prize set count = '$prize_count' where id = '$prize_id'";
                        mysqli_query($con, $sql_count);
                        ?>
                </div>
            </div>
            <div class="footer">
                <img src="img/footer_prize.jpg" alt="">
            </div>
        </div>
    </div>
    <?php } else { ?>
    <div class="container-fluid">
        <div class="container">
            <img src="img/false.jpg" alt="">
        </div>
    </div>
    <?php } ?>
<?php } ?>



<?php 
    $nickname = $_SESSION['user']['nickname'];
    $openid = $_SESSION['user']['openid'];
    mysqli_select_db($con,"数据库名");
    if($prize_content['count'] <= 0) {
        $sql="insert into wx_movie (id,openid,prize_id,prize,whether,prize_code，nickname) values (null,'$openid','$prize_id','奖品已抽完','1','666','$nickname')";
    }else {
        $sql="insert into wx_movie (id,openid,prize_id,prize,prize_code,nickname) values (null,'$openid','$prize_id','$prize_name','$result_rand','$nickname')";    
    }
    mysqli_query($con, $sql);
?>
<?php } else { ?>
<!-- 防止安卓手机刷票   -->
    <div class="container-fluid">
        <div class="container">
            <div class="top">
                <img src="img/false_top.jpg" alt="">
            </div>
            <div class="content">
                <div class="content-footer">                
                    <h2 style="color:black;">不好意思，您已经抽过奖了</h2>
                </div>
            </div>
            <div class="footer">
                <img src="img/footer_prize.jpg" alt="">
            </div>
        </div>
    </div>
<?php } ?>

</body>
</html>