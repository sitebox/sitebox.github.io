<?php

//定义一个数组，用于存放排重后的结果
$result = array();
//读取uid列表文件
$fp = fopen('test.txt','r');

while(!feof($fp))
{
    $uid = fgets($fp);
    $uid = trim($uid);
    $uid = trim($uid, "/r");
    $uid = trim($uid, "\n");

    if($uid == '')
    {
        continue;
    }
    //以uid为key去看该值是否存在
    if(empty($result[$uid]))
    {
        $result[$uid] = 1;
    }
}

fclose($fp);

//将结果保存到文件
$content = '';
foreach($result as $k => $v)
{
    $content .= "$k"."\n";
}
$fp = fopen('result.txt', 'w');
fwrite($fp, $content);
fclose($fp);
?>