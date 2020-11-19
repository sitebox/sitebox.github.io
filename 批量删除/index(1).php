<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>update_index</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="gb2312" />
<link href="style.css" rel="stylesheet">
<script language="javascript" src=common.js></script>
</head>
<body>
<form name=del action="delete.php" method=post>
<?php
$conn=mysql_connect("localhost","root","");
mysql_select_db("update",$conn);
$sql="select * from `data`";
$result=mysql_query($sql);

while($arr=mysql_fetch_array($result))
{
?>
<table border=1>
<tr>
<td><input type=checkbox name=del[] value='<?php echo $arr[id]; ?>'>
</td>
<td><?php echo $arr[id]; ?></td>
<td><?php echo $arr[title]; ?>
</td>
</tr>
</table> 
<?php
}
?>
<input type="submit" name="submit" value="提交">
</form>
</body>
</html>