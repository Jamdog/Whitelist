<?php
	session_start();
	include("../class/main.php");
	$main = new Main(true);
	$_POST['uid'] = filter_var($_POST['uid'],FILTER_SANITIZE_NUMBER_INT);
	$_POST['active'] = filter_var($_POST['active'],FILTER_SANITIZE_NUMBER_INT);
	$updateActive = $main->conn->prepare("UPDATE member SET active=:active WHERE id = :id");
	$updateActive->execute(array('id'=>$_POST['uid'],'active'=>$_POST['active']));	
?>