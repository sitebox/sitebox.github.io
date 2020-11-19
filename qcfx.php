<!DOCTYPE>
<html>
<head>
<title>去除字符串重复行</title>
<meta charset=utf-8">
<style>
#main{
       margin:0 auto;
	   padding:0 100px;
	   font-size:14px;
	   width:900px;
}
h1{
  font-size:20px;
  color:blue;
  text-align:center;

}

</style>
</head>
<body>
<div id="main">
<h1 align=center>文本去重复0.01BETA&nbsp;&nbsp;&nbsp;&nbsp;QQ:78025108</h1>
<form method="post" action="<?php $PATH_INFO;?>">
<textarea name='content' rows='20' cols='40' id="content" ></textarea><br>
<input name="submit" type="submit" value="提交">
<input name="reset" type="reset" value="清除"><br>
</form>
</div>
<?php
@$content=$_POST['content'];                                //将文本域中文本以字符串形式赋给一个变量.
$contente=htmlspecialchars($content);                      //转义HTML符号.
$stra=preg_replace('/($\s*$)|(^\s*^)/m','',$contente);     //消除空行
$strb=strtolower($stra);                                   //把字符串里的大写字母转换成小写.
$arra=explode("\r\n",$strb);                               //注意文本域中换行形式.
$arrb=array_unique($arra);                                 //去重复
sort($arrb);                                               //对去除成功的字符串排序。
		foreach($arrb as $keys=>$value){
          $content=trim($value);
          echo $content."<br>\n";                          //输出去重复并排序的字符串.
}
?>
</body>
</html>
