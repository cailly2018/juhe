<?php
ignore_user_abort();//关闭浏览器后，继续执行php代码
set_time_limit(0);//程序执行时间无限制
$sleep_time = 1;//多长时间执行一次



//$switch为include 'jsonout.php'的返回值

//return 1;//1执行，0不执行
$switch = include 'jsonout.php';
$i = 1;
while($switch){

//这里是想要循环执行的语句
    $t = '我是第';
    $r = '条';
    $msg=$t.$i.$r."\r\n";
    file_put_contents("a.txt",$msg,8);//写入信息
    sleep($sleep_time);//等待时间，进行下一次操作。
    $i++;
}
exit();
?>