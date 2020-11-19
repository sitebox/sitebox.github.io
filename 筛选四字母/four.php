<!doctype html>
<html>
    <head>
	<title>四位.club域名</title>
        <meta charset="utf-8">
    </head>
    <body>
<?php
/*
test.txt文件内容为:

10ferents.club
1901.club
1992.club
288cash.club
2nd.club
365gold.club
3dscanning.club
406disco.club
99fitness.club
abuo9i8n3.club
abuo9i8n5.club
abur5t6b2.club
abut6y7n1.club
abut6y7n4.club
abut6y7n7.club
abuy7u8n6.club
1234.club
abcd.club


4.txt只有:
abcd.club

正确的答案应该是：
1901.club 
1992.club 
1234.club 
abcd.club 
*/
$handle=fopen("test.txt","r");
while(!feof($handle)){
$str=fgets($handle);
if(stripos($str,".")==4){
file_put_contents("4.txt",$str,FILE_APPEND);
}
}
?>
</select><BR>
</body>
</html>
