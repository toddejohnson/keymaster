<?php
/*
Copyright (c) 2012, Todd E Johnson All Rights Reserved.
see LICENSE

RETURNS
0	denied access
1	granted access
2	software error
*/

require_once('MDB2.php');

$db=MDB2::factory('mysql://keymaster:keymaster@localhost/keymaster');
if(PEAR::isError($db)){
	status(2);
}
$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
$db->setOption('portability',MDB2_PORTABILITY_NONE);

if($_GET['name']!=''){
	$name=$_GET['name'];
}else{
	status(2);
}
if($_GET['passcode']!=''){
	$passcode=$_GET['passcode'];
}else{
	status(2);
}
if($_GET['str']!=''){
	$str=$_GET['str'];
}else{
	status(2);
}
// check the controler
$sql="SELECT controlerid,name,passcode FROM controler WHERE name=".$db->quote($name)." AND passcode = ".$db->quote($passcode);
$result=$db->query($sql);
if(PEAR::isError($result)){
	status(2);
}
if($result->numRows()>0){
	$row=$result->fetchRow();
	if($row['name']==$name && $row['passcode']==$passcode){
		$controler=$row['controlerid'];
	}else{
		// controler Invalid controler Auth
		logit(NULL,NULL,NULL,NULL,'DENIED',"Denied access to $name pass $passcode using tag $str. Name pass mismatch.");
		status(0);
	}
}else{
	// controler Invalid controler Auth
	logit(NULL,NULL,NULL,NULL,'DENIED',"Denied access to $name pass $passcode using tag $str. No Rows.");
	status(0);
}
// good controler continue

// check the tag get userid
$sql="SELECT tagid,userid,str,status FROM tags WHERE str=".$db->quote($str);
$result=$db->query($sql);
if(PEAR::isError($result)){
	status(2);
}
if($result->numRows()>0){
	$row=$result->fetchRow();
	if($row['str']==$str){
		$user=$row['userid'];
		$tag=$row['tagid'];
		if($row['status']==0){
			logit($contoler,$user,$tag,NULL,'DENIED',"TAG disabled");
			status(0);
		}
	}else{
		logit($contoler,NULL,NULL,NULL,'DENIED',"TAG MISMATCH Tried $str found ".$row['str']);
		status(0);
	}
}else{
	logit($contoler,NULL,NULL,NULL,'DENIED',"TAG No Rows for tag $str");
	status(0);
}
// good tag have userid.  
// check the user
$sql="SELECT userid,status FROM users WHERE userid=".$db->quote($user,'integer');
$result=$db->query($sql);
if(PEAR::isError($result)){
	status(2);
}
if($result->numRows()>0){
	$row=$result->fetchRow();
	if($row['status']==0){
		logit($contoler,$user,$tag,NULL,'DENIED',"User disabled");
		status(0);
	}
}else{
	logit($contoler,$user,$tag,NULL,'DENIED',"User no rows");
	status(0);
}
// user status good
// check the rules
$sql="SELECT ruleid FROM rules WHERE userid=".$db->quote($user,'integer')." AND controlerid=".$db->quote($controler,'integer');
$result=$db->query($sql);
if(PEAR::isError($result)){
	status(2);
}
if($result->numRows()>0){
	$row=$result->fetchRow();
	$rule=$row['ruleid'];
	logit($contoler,$user,$tag,$rule,'GRANTED',"GRANTED");
	status(1);
}else{
	logit($contoler,$user,$tag,NULL,'DENIED',"Rules no rows");
	status(0);
}
// should never make it here.  wierd if we did.
function logit($controler,$user,$tag,$rule,$stat,$message){
	global $db;
	$sql="
	INSERT INTO log 
		(controlerid, userid, tagid, ruleid, type, message) 
		VALUES (".$db->quote($controler,'integer').",".$db->quote($user,'integer').",".$db->quote($tag,'integer').",".$db->quote($rule,'integer').",".$db->quote($stat).",".$db->quote($message).")
	";
	$result=$db->exec($sql);
	if(PEAR::isError($result)){
		status(2);
	}
}

function status($stat){
	//if($stat==2){die('err');};
	echo "<$stat>\n";
	exit;
}

?>
