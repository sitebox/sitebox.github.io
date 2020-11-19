<?php 
error_reporting(E_ERROR); 
/*
数据库结构:


CREATE TABLE IF NOT EXISTS `demo` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `tel` varchar(15) NOT NULL,
  `money` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- 转存表中的数据 `demo`
--

INSERT INTO `demo` (`id`, `tel`, `money`) VALUES
(1, 'one111', 'one'),
(2, 'two222', 'two'),
(3, 'three333', 'three'),
(4, 'four4433', 'four');


很多地方还是不大明白,只好先备份下来慢慢看了.
*/







define('MSG_SUCCESS', 0);
define('MSG_ERROR',   1);
define('MSG_INFO',    2);


// 在这里配置数据库，其他地方都不用改
$config = array(
// 数据库连接配置
'database' => array(
'dbname' => 'exmp', // 数据库名
'host'   => 'localhost', // 数据库服务器地址
'user'   => 'root', // 数据库用户名
'passwd' => '', // 数据库密码
),
// 配置要处理的数据表
'table' => array(
'name'        => 'demo', // 数据表名称
'primary_key' => 'id', // 主键
'fields'      => array( // 字段，在这里配置了的字段就会显示出来
'id' => array( // 字段 1
'name'       => 'id', // 字段名
'alias_name' => 'id', // 显示在页面上的名称
'editable'   => false, // 是否可编辑 true - 可编辑 false - 不能编辑
),
'tel' => array(
'name'       => 'tel', 
'alias_name' => '电话', 
'editable'   => true,
),
'money' => array(
'name'       => 'money', 
'alias_name' => '金额', 
'editable'   => true,
),
),
),
);


$msg_type = array(
MSG_SUCCESS => array('type' => 'success', 'class' => 'alert-success'),
MSG_ERROR   => array('type' => 'error', 'class' => 'alert-danger'),
MSG_INFO    => array('type' => 'info', 'class' => 'alert-info'),
);
$error_msg = array();


function set_error_msg($error, $msg = '')
{
$GLOBALS['error_msg'] = array('error' => $error, 'msg' => $msg);
}


function get_database() 
{
global $config;
static $db;


if (is_null($db)) {
   $dsn     = "mysql:dbname={$config['database']['dbname']};host={$config['database']['host']}";
   $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';");
   try {
    $db = new PDO($dsn, $config['database']['user'], $config['database']['passwd'], $options);
   } catch (PDOException $e) {
    $db = null;
    set_error_msg(MSG_ERROR, $e->getMessage());
   }
}
return $db;
}
$dbh = get_database();


function get_data_all() 
{
global $dbh, $config;


$sql = sprintf('SELECT * FROM %1$s ORDER BY %2$s DESC', 
$config['table']['name'], 
$config['table']['primary_key']);


$sth = $dbh->query($sql);
return $sth->fetchAll(PDO::FETCH_ASSOC);
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
$msg   = array();
$error = MSG_INFO;


if (trim($_GET['act']) == 'add') {
$data = array();
foreach ($config['table']['fields'] as $field) {
if ($field['editable']) {
$data[$field['name']] = filter_input(INPUT_POST, $field['name']);
}
}
if (empty($data)) {
$msg[] = '没有获取到要新增的数据';
$error = MSG_ERROR;
} else {
$sql = sprintf('INSERT INTO %1$s(%2$s) VALUES (%3$s);', 
$config['table']['name'],
implode(',', array_keys($data)), 
implode(',', array_map(array($dbh, 'quote'), $data)));
$sth = $dbh->prepare($sql);
if ($sth->execute()) {
$msg[] = '新增数据成功';
$error = MSG_SUCCESS;
} else {
$msg[] = $sth->errorInfo();
$error = MSG_ERROR;
}
}
} else { 
$data_delete = array_filter(explode(',', trim($_POST['dels'])), 'is_numeric');
$data_update = array();
foreach ($config['table']['fields'] as $field) {
if ($field['editable']) {
$post = $_POST[$field['name']];
foreach ($post as $k => $v) {
if (in_array($k, $data_delete)) {
continue;
}
$data_update[$k][$field['name']] = trim($v);
}
}
}


if (!empty($data_update)) {
$data_all = get_data_all();
$data_old = array();
foreach ($data_all as $dval) {
$data_old[$dval['id']] = array_intersect_key($dval, current($data_update));
if (!array_diff_assoc($data_update[$dval['id']], $data_old[$dval['id']])) {
unset($data_update[$dval['id']]);
}
}
}


if (!empty($data_update)) {
$sql = array();
foreach ($data_update as $key => $value) {
$data_string = array();
array_walk($value, function ($v, $k) use (&$data_string, $dbh) {
$data_string[] = sprintf('%1$s=%2$s', $k, $dbh->quote($v));
});


$sql[] = sprintf('UPDATE %1$s SET %2$s WHERE %3$s=%4$s', 
$config['table']['name'],
implode(',', $data_string),
$config['table']['primary_key'],
$dbh->quote($key));
}
$sth = $dbh->prepare(implode(';', $sql));
if ($sth->execute()) {
$error = MSG_SUCCESS;
$msg[] = '编辑数据成功';
} else {
$error = MSG_ERROR;
$msg[] = '编辑数据失败';
}
}


if (!empty($data_delete)) {
$sql = sprintf('DELETE FROM %1$s WHERE %2$s IN (%3$s)', 
$config['table']['name'], 
$config['table']['primary_key'], 
implode(',', array_map(array($dbh, 'quote'), $data_delete)));


$sth = $dbh->prepare($sql);
if ($sth->execute()) {
$error = MSG_SUCCESS;
$msg[] = '删除数据成功';
} else {
$error = MSG_ERROR;
$msg[] = '删除数据失败';
}
}
}


set_error_msg($error, implode('; ', empty($msg) ? array('数据没有修改') : $msg));
header(sprintf('Location: ?%1$s', http_build_query($error_msg)));exit;


} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
if (isset($_GET['error']) && isset($_GET['msg'])) {
set_error_msg(intval($_GET['error']), trim($_GET['msg']));
}
$data = get_data_all();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Document</title>
<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.4/css/bootstrap.min.css">
<script src="http://cdn.bootcss.com/jquery/1.11.2/jquery.min.js"></script>
<script src="http://cdn.bootcss.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
<div class="page-header">
 <h1>Demo 页面 <small>blabla...</small></h1>
</div>
<div class="row">
 <div class="col-md-1"></div>
 <div class="col-md-10">
 <?php if (!empty($error_msg)): ?>
  <div class="alert <?php echo $msg_type[$error_msg['error']]['class']; ?>" role="alert">
  <?php echo $error_msg['msg']; ?>
  </div>
 <?php endif; ?>
  <div class="panel panel-default">
   <div class="panel-heading">添加新数据</div>
   <div class="panel-body">
     <form class="form-inline" action="?act=add" method="post">
     <?php foreach ($config['table']['fields'] as $field): ?>
      <?php if ($field['editable']): ?>
      <div class="form-group">
       <label class="sr-only" for="newitem-<?php echo $field['name']; ?>"><?php echo $field['alias_name']; ?></label>
       <input type="text" class="form-control" name="<?php echo $field['name']; ?>" placeholder="输入<?php echo $field['alias_name']; ?>">
      </div>
      <?php endif;?>
     <?php endforeach; ?>
       <button type="submit" class="btn btn-default add-item">新增</button>
     </form>
   </div>
  </div>
  <form method="post" action="">
  <div class="table-responsive">
   <table class="table">
     <thead>
      <tr>
      <?php foreach ($config['table']['fields'] as $field): ?>
      <th><?php echo $field['alias_name']; ?></th>
      <?php endforeach; ?>
      <th>操作</th>
      </tr>
     </thead>
     <tbody>
     <?php foreach($data as $item): ?>
      <tr>
      <?php foreach($item as $_field => $value): ?>
      <td>
      <?php if ($_field == $config['table']['primary_key']): ?>
      <label><input type="checkbox" name="chk[<?php echo $value ?>]" value="<?php echo $value ?>"><?php echo $value ?></label>
      <?php elseif ($config['table']['fields'][$_field]['editable']): ?>
      <input type="text" class="form-control" name="<?php echo $_field ?>[<?php echo $item[$config['table']['primary_key']] ?>]" value="<?php echo $value ?>">
      <?php elseif (isset($config['table']['fields'][$_field])): ?>
      <label><?php echo $value ?></label>
      <?php endif;?>
      </td>
      <?php endforeach; ?>
      <td>
      <button type="button" class="btn btn-default del-item" data-id="<?php echo $item[$config['table']['primary_key']] ?>">删除</button>
      </td>
      </tr>
     <?php endforeach; ?>
     </tbody>
   </table>
   <input type="hidden" id="del-item-hide" name="dels" value="">
  </div>
  <div class="btn-group" role="group" aria-label="...">
   <button type="submit" class="btn btn-default">提交修改</button>
   <button type="reset" class="btn btn-default reset-page">重置</button>
  </div>
  </form>
 </div>
 <div class="col-md-1"></div>
</div>
</div>
<script>
$(function () {
$('.del-item').click(function () {
var $this = $(this),
id = $this.data('id'),
text = ['删除', '取消'];
var isDel = delItem(id);


delItem(id, !isDel);
$this.text(text[+!isDel]).parents('tr').toggleClass('danger');
});


$('.reset-page').click(function () {
window.location.href = '<?php echo $_SERVER['SCRIPT_NAME']; ?>';
});


function delItem(id, deleteIt) {
var $item = $('#del-item-hide');
var store = $item.val().split(',');
var deleteKey = store.indexOf(id + '');
if (deleteIt === undefined) {
return deleteKey > -1;
} else if (!deleteIt && (deleteKey > -1)) {
store[deleteKey] = undefined;
} else if (deleteIt && (deleteKey === -1)) {
store.push(id);
}
$item.val(store.filter(function (v) { return v; }).join(','));
}
});
</script>
</body>
</html>