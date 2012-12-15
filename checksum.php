<?php
/*
Copyright (c) 2012, Todd E Johnson All Rights Reserved.
see LICENSE

Use this to calculate the xor checksum
*/
$var=$argv[1];
print $var1;
$xor=0;
for($i=0;$i<10;$i=$i+2){
  $str=substr($var,$i,2);
  $xor=$xor ^ hexdec($str);
}
print dechex($xor);
