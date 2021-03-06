<?php
/*
*我有一个文本文件file.txt
*内容是每行四个字符:
*fdsa
*vffd
*csfe
*zfvs
*gfge
*fdsk
*dcfa
*zdco
*fdau
*dpkl
*fpyt
*
*这样的结构,有几十万行.我想把他们分类,带aeoiu任一个字符或者多个元音字符的写在
*one.txt文件中,不带aeoiu中的任何一个字符放在文件two.txt中.
*/

$file = file_get_contents('file.txt', 'r'); 

$one = $two = []; 
$lines = explode("\n", $file); 
foreach ($lines as &$line) { 
if (preg_match('/[aeiou]/S', $line)) { 
// if (strpbrk($line, 'aeiou') !== false) { 
// if (strcspn($line, 'aeiou') < strlen($line)) { 
$one[] = &$line; 
} else { 
$two[] = &$line; 
} 
} 
$one = implode("\n", $one); 
$two = implode("\n", $two); 

file_put_contents('one.txt', $one); 
file_put_contents('two.txt', $two);

?>
