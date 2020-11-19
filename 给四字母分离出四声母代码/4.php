<?php
$file = file_get_contents("4letter.txt","r");
$one =array(); 
$two =array(); 
$lines = explode("\n", $file); 
foreach ($lines as &$line) { 
if (preg_match('/[aeiouv]/S', $line)) { 
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