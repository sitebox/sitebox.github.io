<?php
$today=  date('Ymd') ; 									   //今天
$addday = time() + (1 * 24 * 60 * 60);
$tomorrow=date('Ymd', $addday); 						   //明天
$after=time() + (2 * 24 * 60 * 60);
$aftertomorrow=date('Ymd',$after);  					   //后天

$url_day=date('Ymd').".txt";
$url_tom=$tomorrow . ".txt";
$url_after=$aftertomorrow . ".txt";

$jt="http://www.cnnic.cn/download/registar_list/1todayDel.txt";
$mt="http://www.cnnic.cn/download/registar_list/future1todayDel.txt";
$ht="http://www.cnnic.cn/download/registar_list/future2todayDel.txt";

echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
echo "<meta http-equiv=\"Content-Language\" content=\"gb2312\">";
echo "<table border=1 align=center width=400 bgcolor=#DEDFDE>\n";
echo "<caption class='cap'>国内域名删除列表</caption>";
echo "<tr align=center><td class='xtd'>\n";
if(file_exists($url_day)){
     echo "<a href='$url_day'>今天删除列表--$today</a><br>\n";

						  }
else
{
	file_put_contents($url_day, (file_get_contents($jt)));
}
echo "</tr></td>\n";
echo "<tr align=center><td class='xtd'>\n";
if(file_exists($url_tom)){
     echo "<a href='$url_tom'>明天删除列表--$tomorrow</a><br>\n";

						  }
else
{
	file_put_contents($url_tom, (file_get_contents($mt)));
}
echo "</tr></td>\n";
echo "<tr align=center><td class='xtd'>\n";
if(file_exists($url_after)){
     echo "<a href='$url_after'>后天删除列表--$aftertomorrow</a><br>\n";

						  }
else
{
	file_put_contents($url_after, (file_get_contents($ht)));
}
echo "</tr></td>\n";
echo "</table>\n";
?>
