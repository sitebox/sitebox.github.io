<?PHP
//配置部分： 
//注意，如果您没有在后面的注释的地方加上管理员身份验证程序，
//请不要配置正确的数据库用户名和密码！
//采用默认配置运行，程序会给出表单你填写的。

$db_host="localhost";    //数据库服务器
$db_username="root";     //数据库用户名
$db_password="";         //数据库密码
$db_dbname="";          //选择的数据库


//兼容低版本PHP
function requestValues(){
	return ' if(!isset($_POST)){ $_POST = $HTTP_POST_VARS; $_GET = $HTTP_GET_VARS; $_SERVER = $HTTP_SERVER_VARS;} ';
}

eval(requestValues());

$_POST["frametopheight"]=90;  //FrameTop 的高

define("VERSION","4.0"); //版本

error_reporting(1);
@set_time_limit(0);

function num_bitunit($num){
  $bitunit=array(' B',' KB',' MB',' GB');
  for($key=0;$key<count($bitunit);$key++){
	if($num>=pow(2,10*$key)-1){ //1023B 会显示为 1KB
	  $num_bitunit_str=(ceil($num/pow(2,10*$key)*100)/100)." $bitunit[$key]";
	}
  }
  return $num_bitunit_str;
}

//frame 分开标题
function frameset_html(){
	global $_POST;
	return "if(!\$_GET[framename]){
		echo \"<html>
		<head> <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
		<title>MySQL自导入数据库备份程序</title>
		</head>
		<frameset rows='*,0' frameborder='NO' border='0' framespacing='0' name='myframeset'>
			<frame src='\$_SERVER[PHP_SELF]?\$_SERVER[QUERY_STRING]&framename=main' name='mainFrame1'>
			<frame src='about:blank' name='mainFrame2'>  
		</frameset>
		<BODY></BODY>
		</html>\";
		exit;
	}";
}

function postvars_function(){
	return '
	function fsql_StrCode($string,$action="ENCODE"){
		global $_SERVER;
		if($string=="") return "";
		if($action=="ENCODE") $md5code=substr(md5($string),8,10);
		else{
			$md5code=substr($string,-10); 
			$string=substr($string,0,strlen($string)-10); 
		}
		$key = md5($md5code.$_SERVER["HTTP_USER_AGENT"].filemtime($_SERVER["SCRIPT_FILENAME"]));
		$string = ($action=="ENCODE"?$string:base64_decode($string));
		$len = strlen($key);
		$code = "";
		for($i=0; $i<strlen($string); $i++){
			$k = $i%$len;
			$code .= $string[$i]^$key[$k];
		}
		$code = ($action == "DECODE" ? (substr(md5($code),8,10)==$md5code?$code:NULL) : base64_encode($code)."$md5code");
		return $code;
	}
	if($_POST[faisunsql_postvars]){
		if($faisunsql_postvars=unserialize(fsql_StrCode($_POST[faisunsql_postvars],"DECODE"))){
			foreach($faisunsql_postvars as $key=>$value){
				if(!isset($_POST[$key])) $_POST[$key] = $value;
			}
		}else{ die("<script language=\'JavaScript\'>alert(\'由于文档更改,提交信息已丢失,需要重新开始.\');</script>"); }
		unset($_POST[faisunsql_postvars],$faisunsql_postvars,$key,$value);
	}';	
}

eval(frameset_html().postvars_function());

if($_POST["totalsize"]){
	$totalsize_chunk=num_bitunit($_POST["totalsize"]);
}

//css 样式定义
function csssetting(){
  return "<style type='text/css'>
	<!--
	body, td, input, a{color:#985b00;font-family: '宋体';font-size: 9pt;}
	body, td, a{line-height:180%;}
	.tabletitle{color:#FFFFFF;background-color:#FF6600;}
	.tabledata{background-color:#FFEECC;}
	.tabledata_on{background-color:#FFFFCC;}	
	input, .borderdiv {border:1px inset;}
	-->
	</style>";
}

//各页相同的页面头
function fheader(){
	global $_POST;
	$str = fsql_StrCode(serialize($_POST),"ENCODE");
	echo "<html>
	<head> 
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
	<title>MySQL自导入数据库备份程序</title>
	".csssetting()."
	</head><body link='#0000FF' vlink='#0000FF' alink='#0000FF' bgcolor='#FFFFFF'>
	<center><script language='Javascript'>document.doshowmywin=1;</script>
	<form name='myform' method='post' action=''><input type='hidden' name='faisunsql_postvars' value='".$str."'>";
}

function showmywin_script(){
	global $_POST;
	return "<script language='Javascript'>
	function showmywin(){
		if(!document.doshowmywin) return;
		if(top.myframeset&&this.window.name=='mainFrame1'){
			top.myframeset.rows='$_POST[frametopheight],*,0';
		}
		if(top.myframeset&&this.window.name=='mainFrame2'){
			top.myframeset.rows='$_POST[frametopheight],0,*';
		}
	}
	document.body.onload=showmywin;
	</script>";
}

//各页相同的页面尾
function ffooter(){
	echo "<div id='pageendTag'></div></form>
	<a href='http://www.hbwanghai.com' target='_blank'>河北网海科技空间域名注册</a><br>
	免费程序 欢迎宣传、发布<br>
	</center>".showmywin_script()."</body></html>";
}

//开始说明表格
function tabletext($ttext="",$twidth=400){
	return "<table width='$twidth' border='0' cellspacing='1' cellpadding='3' align=center><tr><td>$ttext</td></tr></table><br>\r\n";
}

//开始一个表格
function tablestart($ttitle="",$twidth=400){
	return "<table width='1' border='0' cellspacing='0' cellpadding='0' align=center class='tabletitle'>
	<tr><td class='tabletitle'><strong>&nbsp;$ttitle</strong></td></tr> <tr><td>
	<table width='$twidth' border='0' cellspacing='1' cellpadding='2' align=center>";
}

//插入数据到表格
function tabledata($data,$widths=""){
	$pdata=explode("|",$data);
	$pwidths=explode("|",$widths);
	$str="<tr class='tabledata' onmouseover='this.className=\"tabledata_on\";' onmouseout='this.className=\"tabledata\";'>\r\n";
	for(@reset($pdata);@list($key,$val)=@each($pdata);){
		$str.="\t<td style='padding-left:4px' ".(intval($pwidths[$key])?"width='$pwidths[$key]'":"")." nowrap>$pdata[$key]</td>\r\n";
	}
	$str.="</tr>\r\n";
	return $str;
}

//结束一个表格
function tableend(){
	return "</table></td></tr></table><BR>\r\n";
}

//按钮样式
function fbutton($type="submit",$name="Submit",$value="确定",$script="",$return=0){
	$str="<input type='$type' name='$name' value='$value' class='tabletitle' style='border:3px double #FF6600' $script> ";
	if($return) return $str;else echo $str;
}

//topFrame
if($_GET["action"]=="topframe"&&$_GET["framename"]=="topframe"){
	fheader();
	echo "</font></center></body></html>";
	exit;
}


/* 
如果您在开头配置部分填写了正确的配置，
您可以在这里加上管理员身份验证，
并把下面声明常量的语句用在代码中：
define("IS_ADMIN","yes");  //用于检验是否加了管理员身份验证代码。
*/

if(!isset($_POST[dosubmit])){
	$_POST["db_host"]=$db_host;
	$_POST["db_username"]=$db_username;
	$_POST["db_password"]=$db_password;
	$_POST["db_dbname"]=$db_dbname;
}

// 配置表单
if(!@mysql_connect($_POST[db_host],$_POST[db_username],$_POST[db_password])||!@mysql_select_db($_POST[db_dbname])){
	fheader();
	if(isset($_POST['finByte']) and isset($_POST['db_dbname'])){
		echo "连接数据库超时,请<a href='javascript:submitme();'>刷新重试</a>.<font id='timeescapepls'>10</font>秒后将自动重试.<BR>也可能是配置不对,请检查您的配置.<BR>";
		echo "
		<script language='JavaScript'>
		document.doshowmywin=0;
		retryTime=10;
		function timeescape(){
			if(!retryTime) return;
			timeescapepls.innerHTML=retryTime--;
		}setInterval('timeescape()',1000);
		function submitme(){
			for(i=myform.elements.length-1;i>=0;i--){if(myform.elements[i].name==\"action\")break;}
			myform.elements[i].value=\"databackup\";
			myform.submit();
		}
		setTimeout('submitme()',retryTime*1000);
		</script>";
	}
	else if(isset($_POST[dosubmit])){
		echo "<script language='JavaScript'>alert('连接数据库错误,请检查您的配置.');</script>";
	}
	echo tabletext("输入正确的配置以连接数据库。<br> 如果无法连接数据库，请联系您的主机管理员以获得正确配置值。");
	echo tablestart("请检查您的配置");
	echo tabledata("服务器主机：|<input name='db_host' value='$_POST[db_host]' type='text'>");
	echo tabledata("要导出的数据库：|<input name='db_dbname' value='$_POST[db_dbname]' type='text'>");
	echo tabledata("数据库用户名：|<input name='db_username' value='$_POST[db_username]' type='text'>");
	echo tabledata("数据库密码：|<input name='db_password' value='$_POST[db_password]' type='password'>");
	echo tableend();
	fbutton('submit','dosubmit','连接');
	fbutton('reset','doreset','重置');
	ffooter();
	exit;
}
mysql_query("SET NAMES utf8;");

if(!defined("IS_ADMIN") and !isset($_POST['db_dbname'])){die("您在程序开头部分填写了正确的配置，但没有加上任何管理员身份验证代码。为了安全，请不要配置正确的数据库用户名和密码！");}

//选择要备份的数据表
if (!isset($_POST['action'])){
	$currow=mysql_fetch_array(mysql_query("select version() as v"));
	$_POST['mysql_version']=$currow['v'];
	fheader();
	echo tabletext("以下列出的是该数据库中所有的数据表。<br>默认情况下全部导出，您也可以选择只导出其中一部分表。",500);
	echo tablestart("请选择要备份的数据表 &nbsp; (当前数据库版本: $_POST[mysql_version])",500);
	echo tabledata("<center><B><a href='#' onclick='selrev();return false;'>[反选]</a></B></center>|<strong>表名</strong>|<strong>注释</strong>|<strong>记录数</strong>|<strong>大小</strong>","10%|30%|30%|17%|23%");
	$query=mysql_query("SHOW TABLE STATUS");
	while ($currow=mysql_fetch_array($query)){
		echo tabledata("<center><input name='fsqltable[{$currow[Name]}]' id='fsqltable_$currow[Name]' type='checkbox' value='".($currow[Data_length]+$currow[Index_length]).",".$currow[Avg_row_length]."' checked onclick='getsize()'></center>|<label for='fsqltable_$currow[Name]'>$currow[Name]</label>|$currow[Comment]|$currow[Rows]|".num_bitunit($currow[Data_length]+$currow[Index_length])."");
	}
	echo tabledata("<center><B><a href='#' onclick='selrev();return false;'>[反选]</a></B></center>|<B>目前选择表的总大小：</B>|&nbsp;|&nbsp;|<B><label id='totalsizetxt'></label></B>");
	echo tableend();
	echo "<script language='JavaScript'>
	<!--
	  function selrev() {
		with(myform) {
			for(i=0;i<elements.length;i++) {
				thiselm = elements[i];
				if(thiselm.name.match(/fsqltable\[\w+\]/))
					thiselm.checked = !thiselm.checked;
			}
		}
		getsize();
	  }
	 	
	  function num_bitunit(num){
		 var bitunit=new Array(' B',' KB',' MB',' GB');
		 for(key=0;key<bitunit.length;key++){
		   if(num>=Math.pow(2,10*key)-1){ //1023B 会显示为 1KB
			  num_bitunit_str=(Math.ceil(num/Math.pow(2,10*key)*100)/100)+' '+bitunit[key];
		   }	 
		 }
		 return num_bitunit_str;
	  }
	
	  function getsize(){
		var ts=0;
		with(document.myform) {
			for(i=0;i<elements.length;i++) {
				thiselm = elements[i];
				if(thiselm.name.match(/fsqltable\[\w+\]/))
					ts += parseInt(thiselm.value);
			}
			totalsizetxt.innerHTML=num_bitunit(ts);
		}
	  }
	  getsize();
	-->
	</script>
	<input name='action' type='hidden' id='action' value='selecttype'>";
	fbutton('submit','dosubmit','下一步');
	fbutton('reset','doreset','重置',"onmouseup=setTimeout('getsize()',100)");
	ffooter();
}

//选择导出方式
if($_POST['action']=="selecttype"){
	$_POST["totalsize"]=0;
	for(@reset($_POST[fsqltable]);count($_POST[fsqltable])&&@list($key,$val)=@each($_POST[fsqltable]);) {
		$_POST["totalsize"]+=$val;
	}
	fheader();
	$_POST["totalsize"]>1024*1024 ? $partsaveck="checked" : $downloadck="checked";
	echo tabletext("选择导出单文件还是多文件：<br>
					数据库太大的话，建议选择多文件导出方式。<br>
					系统会根据所要导出数据量的大小，给出推荐的默认值，<br>
					如果您无法判断您的数据量大小，按默认选定即可。",500);
	echo tablestart("请选择导出方式",500);
	echo tabledata("导出方式：|<br>
					<input name='back_type' value='download' type='radio' $downloadck>生成单个文件并下载 (备份的数据量较大时不建议使用)<br>
					<input name='back_type' value='partsave' type='radio' $partsaveck>分为多个文件并保存在服务器 <br><br>");
	echo tableend();
	echo "
	<script language='JavaScript'>
	function confirmit(){
		with(myform){
			if(back_type[0].checked && ".intval($_POST["totalsize"]).">1024*1024 && !confirm(\"您要导出的数据量比较多（{$totalsize_chunk}），建议选择多文件导出方式。\\r\\n点击“确定”继续导出单文件，“取消”返回更改。\"))
				return false;
		}
		return true;
	}
	myform.onsubmit=new Function('return confirmit();');
	</script>
	<input name='action' type='hidden' id='action' value='selectoption'>";
	fbutton('submit','dosubmit','下一步');
	fbutton('reset','doreset','重置');
	ffooter();
}


if($_POST['action']=="selectoption"){
	if($_POST['back_type']=="partsave"){//多文件保存选项
		fheader();
		echo tabletext("您选择了多文件导出方式，总数据量 $totalsize_chunk 字节。<br><br>
						导出的文件将包括一个主文件和多个数据文件，都放在同一个目录下。<br>
						每个数据文件不宜过大，否则容易造成导出或导入超时；而设置得越小则导出的页数越多。<br>
						导入密码在数据库导入时和HTTP方式下载数据文件时使用，请务必牢记。",500);
		echo tablestart("保存选项：",500);
		echo tabledata("存放目录：|<input name='dir' value='{$_POST[db_dbname]}data' type='text' size=20>|相对本程序所在目录，必须有写入权限");
		echo tabledata("主文件名：|<input name='filename' value='index' type='text' size=16>.php|不含扩展名！");
		echo tabledata("生成文件格式：|<input name='extension' value='php' type='radio' checked>.php | 生成php文件可直接上传到新服务器恢复");
		echo tabledata("每个数据文件大小：|<input name='filesize' value='1000' type='text' size=10>|单位 KB，1 MB = 1024 KB");
		echo tabledata("导出一页时间间隔：|<input name='nextpgtimeout' value='0' type='text' size=10>|秒，若您的空间不支持频繁提交请设大一点");   
		echo tabledata("数据导入密码：|<input name='back_pass' value='' type='password' size=10>|导入和HTTP下载.php文件时的密码");   
		echo tabledata("确认导入密码：|<input name='back_pass2' value='' type='password' size=10>|不添默认为lvtao.net");
		echo tableend();
		echo "
		<script language='JavaScript'>
		function confirmit(){
			with(myform){
				if(back_pass.value!=back_pass2.value){
					alert('导入密码两次输入密码必须相同。');
					return false;
				}
			}
			return true;
		}
		myform.onsubmit=new Function('return confirmit();');
		</script>
		<input name='action' type='hidden' id='action' value='databackup'>";
		fbutton('submit','dosubmit','下一步');
		fbutton('reset','doreset','重置');
		ffooter();
	}

	if($_POST['back_type']=="download"){//单文件下载选项
		fheader();
		echo tabletext("您选择了单文件导出方式，总数据量 $totalsize_chunk 字节。",500);
		echo tablestart("单文件导出：",500);
		echo tabledata("导出文件名：|<input name='sqlfilename' value='$_POST[db_dbname](".date("Ymd").")_Mysql_txt.php' type='text' size='40'>");
		echo tabledata("生成文件格式：|<input name='extension' value='php' type='radio' checked>.php");
		echo tableend();
		echo "<input name='action' type='hidden' id='action' value='databackup'>";
		fbutton('submit','dosubmit','导出');
		fbutton('reset','doreset','重置');
		ffooter();
	}
}

if($_POST['action']=="databackup"){

	function escape_string($str){
		$str=mysql_escape_string($str);
		$str=str_replace('\\\'','\'\'',$str);
		$str=str_replace("\\\\","\\\\\\\\",$str);
		$str=str_replace('$','\$',$str);
		return $str;
	}

	function sqldumptable($table,$tableid,$part=0) {
		if($part) global $lastcreate_temp,$current_size,$_POST;;

		//structure
		if($tableid>=intval($_POST[nextcreate]) or $part==0){
			@mysql_query("SET SQL_QUOTE_SHOW_CREATE = 1");
			$query=mysql_query("SHOW CREATE TABLE `$table`");
			$row=mysql_fetch_row($query);
			$sql=str_replace("\n","\\n",str_replace("\"","\\\"",$row[1]));
			$sql=preg_replace("/^(CREATE\s+TABLE\s+`$table`)/mis","",$sql);
			$dumpstring="create(\"$table\",\"$sql\");\r\n\r\n";
			$_POST[nextcreate]++;
			dealdata($dumpstring);
			mysql_free_result($query);
		}		

		//data
		$query = mysql_query("SELECT count(*) as count FROM `$table` ");
		$count = mysql_fetch_array($query);
		$query = mysql_query("SELECT * FROM `$table` limit ".intval($_POST[lastinsert]).",$count[count] ");
		$numfields = mysql_num_fields($query);
		$dump_values = "";
		while ($row = mysql_fetch_row($query)) {
			$dump_values .= ($dump_values?",\r\n":"")."(";
			for ($i=0;$i<$numfields;$i++) {
				if(stristr(mysql_field_flags($query,$i),"BINARY")){ //二进制处理
					$row[$i] = '\''."\".base64_decode('".base64_encode(addslashes($row[$i]))."').\"".'\'';
				}else if (!isset($row[$i]) or is_null($row[$i])) {
					$row[$i] = "NULL";
				}else {
					$row[$i] = '\''.escape_string($row[$i]).'\'';
				}
			}
			$dump_values .= implode(",",$row).")";
			$value_stop = 0;
			$value_len = strlen($dump_values);

			$_POST[lastinsert]++;

			if($value_len>100000 || ($part && $current_size+$value_len>=intval($_POST["filesize"])*1024)){ //0.1M 左右
				$dumpstring = "insert(\"$dump_values\");\r\n\r\n";
				dealdata($dumpstring);
				$dump_values = "";
			}
		}
		if($dump_values){
			$dumpstring = "insert(\"$dump_values\");\r\n\r\n";
			dealdata($dumpstring);
		}
		mysql_free_result($query);
		
		//end of table
		$dumpstring = "tableend(\"$table\");\r\n\r\n";
		dealdata($dumpstring);
		$_POST[tabledumping]++;
		$_POST[lastinsert]=0;
	}

	function timeformat($time){
		return substr("0".floor($time/3600),-2).":".substr("0".floor(($time%3600)/60),-2).":".substr("0".floor($time%60),-2);
	}	
	function mysql_functions(){
		return '
		@mysql_connect($_POST[db_host],$_POST[db_username],$_POST[db_password]) or die("<div id=pageendTag></div><BR><BR><center>不能连接服务器或连接超时！请返回检查您的配置。</center> $showmywin0");
		if(!@mysql_select_db($_POST[db_dbname])){
			global $_POST;
			if(!$_POST[db_autocreate]){echo "<div id=pageendTag></div><BR><BR><center>数据库[{$_POST[db_dbname]}]不存在！请返回检查您的配置。</center> $showmywin0";exit;	}
			if(!mysql_query("CREATE DATABASE `$_POST[db_dbname]`")){echo "<div id=pageendTag></div><BR><BR><center>数据库[{$_POST[db_dbname]}]不存在且自动创建失败！请返回检查您的配置。</center> $showmywin0";exit;}
			mysql_select_db("$_POST[db_dbname]");
		}mysql_query("SET NAMES utf8;");
		function query($sql){
			global $_POST;
			if(!mysql_query($sql)){
				echo "<BR><BR><font color=red>MySQL语句错误！您可能发现了程序的BUG！<a href=\"mailto:faisun@sina.com\">请报告开发者。</a>
				  	<BR>版本：V'.VERSION.'<BR>语句：<XMP>$sql</XMP>错误信息： ".mysql_error()." </font>" ;
				if(trim($_POST[db_temptable])) query("DROP TABLE IF EXISTS `$_POST[db_temptable]`;");
				exit;
			}
		}
		function create($table,$sql){
			global $_POST;
			if(!trim($_POST[db_temptable])){
				do{
					$_POST[db_temptable]="_faisunsql".rand(100,10000);
				}while(@mysql_query("select * from `$_POST[db_temptable]`"));
			}
			query("CREATE TABLE `$_POST[db_temptable]` $sql");
			if(!$_POST[db_safttemptable]) query("DROP TABLE IF EXISTS `$table`;");
		}
		function insert($data){
			global $_POST;
			query("INSERT IGNORE INTO `$_POST[db_temptable]` VALUES $data;");
		}
		function tableend($table){
			global $_POST;
			if($_POST[db_safttemptable]) query("DROP TABLE IF EXISTS `$table`;");
			query("ALTER TABLE `$_POST[db_temptable]` RENAME `$table`");
		}';
	}
	
	function auto_submit_script(){
		return "echo \"<script language='Javascript'>
				try{finisheditem.focus();}catch(e){}
				function checkerror(frame){
					if(top.mainFrame1.location.href!=top.mainFrame2.location.href||(frame.document && !frame.document.all.postingTag && frame.document.all.pageendTag)){
						postingTag.innerHTML='MySQL:提交出现错误.正在自动<a href=\'javascript:myform.submit();\'>重新提交</a>...';
						myform.submit();
					}
				}
				nextpgtimeout = parseFloat('\$_POST[nextpgtimeout]')?parseFloat('\$_POST[nextpgtimeout]'):0;
				if(top.myframeset&&this.window.name=='mainFrame1'){
					myform.target='mainFrame2';
					setInterval('checkerror(top.mainFrame2)',10000+1000*nextpgtimeout);
				}
				if(top.myframeset&&this.window.name=='mainFrame2'){
					myform.target='mainFrame1';
					setInterval('checkerror(top.mainFrame1)',10000+1000*nextpgtimeout);
				}
				setTimeout('myform.submit();',1000*nextpgtimeout);
				</script>\";
		";
	}
	
	if($_POST[back_type]=="partsave"): ////////////////////////   Save Data ////////////////////////////

		if(!$_POST[tabledumping]) $_POST[tabledumping]=0; //正在导出的表
		if(!$_POST[nextcreate]) $_POST[nextcreate]=0; //待建立的表
		if(!$_POST[lastinsert]) $_POST[lastinsert]=0;
		if(!$_POST[page]) $_POST[page]=0;

		if(!is_dir("$_POST[dir]") and !@mkdir("$_POST[dir]",0777)){
			fheader();echo "<BR><BR><center>目录'$_POST[dir]'不存在且不能自动创建！请检查目录权限（权限为 777 方可写文件）。</center><BR><BR>";ffooter();exit;
		}
		@chmod("$_POST[dir]",0777);

		//是否有多余的文件
		$dfileNo=0;
		$open=opendir($_POST["dir"]);
		$delhtml="";
		while($afilename=readdir($open) and !$_POST[filedeled]){
			$checked="";
			if(substr($afilename,0,strlen($_POST[filename]))==$_POST[filename]){
				$checked="checked";
			}
			if(is_file("$_POST[dir]/$afilename")){
				$delhtml.=tabledata("$afilename|".date("Y-m-d",filectime("$_POST[dir]/$afilename"))."|".num_bitunit(filesize("$_POST[dir]/$afilename"))."|<center><input name='dfile[$dfileNo]' type='checkbox' value='$_POST[dir]/$afilename' $checked></center>");
				$dfileNo++;
			}
		}

		//多余文件处理
		if($dfileNo){
			$_POST[filedeled]=1;
			fheader();
			echo tabletext("'$_POST[dir]/'中以下文件已存在，它们可能被覆盖或成为额外的文件。<br>您可以有选择地删除它们或返回上一步重新设定：",500);
			echo tablestart("选择要删除的文件：",500);
			echo tabledata("<strong>文件名</strong>|<strong>修改日期</strong>|<strong>大小</strong>|<center><strong>反选</strong><input type='checkbox' name='checkbox' value='' onclick='selrev();'></center>","31%|32%|21%|16%");
			echo $delhtml;
			echo tableend();
			echo "
			<script language='JavaScript'>
			function selrev() {
				with(myform) {
					for(i=0;i<elements.length;i++) {
						thiselm = elements[i];
						if(thiselm.name.match(/dfile\[\w+\]/))	thiselm.checked = !thiselm.checked;
					}
				}
			}
			</script>";
			fbutton('submit','dosubmit','删除并继续');
			fbutton('reset','doreset','重置');
			fbutton('button','dogoback','返回修改','onclick=\'history.back();\'');
			ffooter();
			exit;
		}

		//删除多余文件
		if($_POST[filedeled]==1){
			for(@reset($_POST["dfile"]);@list($key,$val)=@each($_POST["dfile"]);){
				if($val) unlink($val);
			}
			unset($_POST["dfile"]);
		}
		$_POST[filedeled]=2;

		//开始导出前的预处理
		if($_POST[page]==0){
			if(!file_exists($_POST[dir]))mkdir($_POST[dir], 0777);
			$_POST[page]=1;
			if(is_writable($_POST[dir])){
			fheader();
			echo tablestart("目录权限正确");
			echo tabledata("<br>经测试，该目录可以写入文件。<br><br>");
			echo tableend();
			fbutton('submit','dosubmit','开始自动导出');
			ffooter();
			exit;
			}
		}


		
		if(!$_POST["StartTime"]) $_POST["StartTime"]=time();

		$writefile_data = '';
		
		function writefile($data,$method='w'){
			global $fsqlzip,$_POST;;
			$file = "{$_POST[filename]}_pg{$_POST[page]}.php";
			$fp=fopen("$_POST[dir]/$file","$method");
			flock($fp,2);
			fwrite($fp,$data);
		}

		$current_size = 0;
		function dealdata($data){
			global $current_size,$tablearr,$writefile_data,$_POST;;
			$current_size += strlen($data);
			$writefile_data .= $data;
			if($current_size>=intval($_POST["filesize"])*1024){
				$current_size=0;
				$writefile_data .= "\r\n?".">";

				writefile($writefile_data,"w");

				$_POST[page]=intval($_POST[page])+1;

				fheader();
				echo tablestart("正在从数据库'$_POST[db_dbname]'中导出数据……",500);

				$str1="<br>-= 以下数据表处理完成 =- <div class='borderdiv' style='width:150px;height:100px;overflow:auto;' align=left>";
				
				$finishByte=0;
				for(reset($tablearr);list($key,$val)=each($tablearr);){
					if($key<$_POST[tabledumping]){
						$str1.="√ $val<BR>\r\n";
						$finishByte+=$_POST[fsqltable][$val];
					}else if($key==$_POST[tabledumping]){
						$str1.="<a href='#' id='finisheditem'> </a></div>
						<br>-= 以下数据表正待处理 =-
						<div class='borderdiv' style='width:150px;height:100px;overflow:auto;' align=left>
						<font style='color:#FF0000'>→ $val</font><br>\r\n";
						$finishByte+=$_POST[lastinsert]*substr(strstr($_POST[fsqltable][$val],','),1);
						$finish=intval($finishByte/$_POST[totalsize]*100);						
					}else{
						$str1.="・ $val<br>\r\n";
					}
				}
				$str1.="</div><BR>";

				$str2=tablestart("导出状态",300);
				$str2.=tabledata("共有数据：|".num_bitunit($_POST[totalsize])."","100|200");
				$str2.=tabledata("现已导出：|".num_bitunit($finishByte)."");
				$str2.=tabledata("每页导出：|".num_bitunit(intval($finishByte/$_POST[page]))."");
				$str2.=tabledata("导出时间间隔：|$_POST[nextpgtimeout] 秒");
				$str2.=tabledata("每页生成数据文件|≥ ".num_bitunit($_POST["filesize"]*1024)."");
				$str2.=tabledata("已生成数据文件：|".($_POST[page]-1)." 个");
				$str2.=tabledata("正在自动进入：|<a href='javascript:myform.submit();'>第 $_POST[page] 页</a>");
				$str2.=tabledata("已用时：|".timeformat(time()-$_POST["StartTime"])."");
				$str2.=tabledata("已完成：|{$finish}% ");
				$str2.=tabledata("完成进度：|<table width=100% height=12  border=0 cellspacing=1 cellpadding=0 class='tabletitle' align=center><tr><td width='$finish%'><div></div></td><td width='".(100-$finish)."%'  class='tabledata'><div></div></td></tr></table>");
				$str2.=tableend();
				$str2.="<B><div id='postingTag'></div></B>";
				echo tabledata("$str1|$str2");
				echo tableend();
				ffooter();
				eval(auto_submit_script());
				exit();
			}
		}


// 开始导出一页
$writefile_data = "<?\r\nif(!defined('VERSION')){echo \"<meta http-equiv=refresh content='0;URL={$_POST[filename]}.php'>\";exit;}\r\n";

$tablearr=array();
for(@reset($_POST[fsqltable]);count($_POST[fsqltable])&&@list($key,$val)=@each($_POST[fsqltable]);) {
	$tablearr[]=$key;
}

for($i=$_POST[tabledumping];$i<count($tablearr);$i++){
	sqldumptable($tablearr[$i],$i,1);  //导出表
}

//结束最后文件
$data="echo '<center><BR><BR><BR><BR>完成。所有数据都已经导入数据库中。</center>'; exit; ?".">";

$writefile_data .= "$data";
writefile($writefile_data,"w");
$backpassword=$_POST[back_pass]?$_POST[back_pass]:"lvtao.net";
		//引导文件内容
$data='<?

$usedumppass=1;  //导入数据时是否使用导入密码。如果您忘记了导入密码，请把值改为 0 。HTTP方式下载数据文件不能取消导入密码。

define("VERSION","'.VERSION.'");
error_reporting(1);
@set_time_limit(0);
$md5pass="'.md5($backpassword).'";

'.requestValues().frameset_html().postvars_function().'?'.'><html><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>MySQL 数据库自导入程序</title>'.csssetting().'</head>
<body link="#0000FF" vlink="#0000FF" alink="#0000FF">
<center>

<script language="Javascript">document.doshowmywin=1;</script>		
'.showmywin_script().'
<?
$showmywin0=$_POST[loadpage]?"<script language=Javascript>document.doshowmywin=0;</script></body>":"";
	if(!$_POST["action"] and !$_GET["action"]){
?'.'><center><form name="configform" method="post" action="">'.
tablestart('备份信息一览').
tabledata("共有数据量：|".num_bitunit($_POST[totalsize])."","50%|50%").
tabledata("共有数据表：|".count($_POST[table])).
tabledata("每页生成数据文件|≥ ".num_bitunit($_POST["filesize"]*1024)).
tabledata("数据文件数：|".$_POST[page]).
tabledata("文件总数：|".($_POST[page]+2)).
tabledata("备份时间：|".date("Y-m-d H:i")).
tabledata("原数据库版本：|".$_POST[mysql_version]).
tableend().
tablestart('导入数据库配置').
tabledata('服务器：|<input name="db_host" value="'.$_POST[db_host].'" type="text">',"50%|50%").
tabledata('数据库：|<input name="db_dbname" value="'.$_POST[db_dbname].'" type="text">').
tabledata('该数据库不存在时自动创建|<input name="db_autocreate" value="1" type="checkbox" checked>').
tabledata('用户名：|<input name="db_username" value="root" type="text">').
tabledata('密　码：|<input name="db_password" value="" type="password">').
tabledata('导入一页时间间隔：|<input name="nextpgtimeout" value="'.$_POST[nextpgtimeout].'" type="text"> 秒').
tabledata('导入密码：|<input name="db_pass" value="" type="password">').
tabledata('安全的临时表(<a href="javascript:alert(\'使用临时表插入完整无误的数据后再删除原表,要临时占用数据库空间.\');" title="帮助">?</a>)：|<input name="db_safttemptable" type="checkbox" id="db_safttemptable" value="yes" checked>').
tableend().
fbutton('submit','action','导入','',1).
'</form>
</center>
<?
exit;
}
if($usedumppass and md5($_POST[db_pass])!=$md5pass) die("<div id=pageendTag></div>导入密码不正确！如果您忘记了导入密码，请把本源文件开头的 \$usedumppass 的值改为 0 。 $showmywin0");
'.mysql_functions().'

$totalpage='.$_POST[page].';
if(!$_POST[loadpage]){$_POST[loadpage]=1;}
if($totalpage>=$_POST[loadpage]){
include("'.$_POST[filename].'_pg$_POST[loadpage].php");
echo "<center><form name=myform method=\'post\' action=\'\'>";
$_POST[loadpage]++;

echo "<input type=\'hidden\' name=\'faisunsql_postvars\' value=\'".fsql_StrCode(serialize($_POST),"ENCODE")."\'>
<BR><BR>正在导入数据到数据库\'$_POST[db_dbname]\'……<BR><BR>本页运行完成！ 正在自动进入<a href=\'javascript:myform.submit();\'>第 $_POST[loadpage] 页</a>，共 $totalpage 页……
<BR><BR>(除非进程长久不动，否则请不要点击以上页码链接。)";
?'.'>
<BR><BR><B><div id="postingTag"></div></B>
<? '.auto_submit_script().' ?'.'>
<div id="pageendTag"></div>
</form></center><?php }else{echo "<center>完成导入数据到数据库\'$_POST[db_dbname]\'</center>";}?>
</body></html>';

		//写入引导文件
		if(isset($fsqlzip)){
			$fsqlzip->addfile($data,"$_POST[filename].php","$fsqlzip->gzfilename.tmp");
			rename("$fsqlzip->gzfilename.tmp","$fsqlzip->gzfilename");
		}else{
			$file="$_POST[dir]/$_POST[filename].php";
			$fp=fopen($file,"w");
			flock($fp,2);
			fwrite($fp,$data);
			fclose($fp);
		}

		//提示导出完成
		fheader();
		echo tabletext("<BR><BR>全部完成，用时 ".timeformat(time()-$_POST["StartTime"])." 。
		<BR><BR>数据库'$_POST[db_dbname]'已全部保存到文件夹'$_POST[dir]'中，共 ".intval($_POST[page])." 页，".(intval($_POST[page])+2)." 个文件。
		<BR><BR>将此文件夹置于服务器可访问目录，并运行'$_POST[filename].php'即可将数据导入。>
		或在<a href='$_POST[dir]/{$_POST[filename]}.php' target='_blank'><H3>运行备份文件 {$_POST[filename]}.php </H3></a>时也会出现此链接。
		<BR><BR>",500);
		echo "<div id='postingTag'></div>";
		ffooter();
		exit;
	endif;
}
?>