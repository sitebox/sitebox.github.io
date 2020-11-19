<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>delete</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="gb2312" />
<link href="style.css" rel="stylesheet">
<script language="javascript" src=common.js></script>
</head>
<body>
<?php
$id_arr	=$_POST[del];	//数组格式：1,2,3,4
$id	=implode(",",$id_arr);	//字符串格式：1,2,3,4

$sql="delete from `data` where id in ($id)";
$conn=mysql_connect("localhost","root","");
mysql_select_db("update",$conn);
$result=mysql_query($sql);
echo "删除成功";
?>
</body>
</html>
