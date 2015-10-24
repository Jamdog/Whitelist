<?php
	session_start();
	include("../class/main.php");
	$main = new Main(true);
	$_POST['w'] = filter_var($_POST['w'],FILTER_SANITIZE_STRING);
	$_POST['s'] = filter_var($_POST['s'],FILTER_SANITIZE_NUMBER_INT);
	$_POST['mid'] = filter_var($_POST['mid'],FILTER_SANITIZE_NUMBER_INT);
	if ($_POST['id'] == 'new') {
		//insert message
		$insertMessage = $main->conn->prepare("INSERT INTO warnings (member_id,reason,severity,original) VALUES (:member_id,:reason,:severity,:original)");
		$insertMessage->execute(array('member_id'=>$_POST['mid'],'reason'=>$_POST['w'],'severity'=>$_POST['s'],'original'=>$_POST['org']));
		$newId = $main->conn->lastInsertId();
		$return = array('newid'=>$newId);
	} elseif ($_POST['w'] == '') {
		//remove message
		$deleteMessage = $main->conn->prepare("DELETE FROM warnings WHERE id = :id");
		$deleteMessage->execute(array('id'=>$_POST['id']));
		$return = array('newid'=>'');
	} else {
		//update message
		$_POST['id'] = filter_var($_POST['id'],FILTER_SANITIZE_NUMBER_INT);
		$updateMessage = $main->conn->prepare("UPDATE warnings SET reason = :reason,severity=:severity,original=:original WHERE id = :id");
		$updateMessage->execute(array('reason'=>$_POST['w'],'severity'=>$_POST['s'],'id'=>$_POST['id'],'original'=>$_POST['org']));
		$return = array('newid'=>'');
	}
	echo json_encode($return);
?>