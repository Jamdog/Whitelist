<?php
	session_start();
	include("../class/main.php");
	$main = new Main(true);
	$_POST['uid'] = filter_var($_POST['uid'],FILTER_SANITIZE_NUMBER_INT);
	$_POST['group'] = filter_var($_POST['group'],FILTER_SANITIZE_NUMBER_INT);
	$_POST['add'] = filter_var($_POST['add'],FILTER_SANITIZE_NUMBER_INT);
	$checkGroup = $main->conn->prepare("SELECT * FROM member_group_link WHERE member_id = :member_id AND group_id = :group_id");
	$checkGroup->execute(array('member_id'=>$_POST['uid'],'group_id'=>$_POST['group']));
	if ($checkGroup->rowCount() > 0 && $_POST['add'] == 0) {
		//remove
		$deleteGroupLink = $main->conn->prepare("DELETE FROM member_group_link WHERE member_id = :member_id AND group_id = :group_id");
		$deleteGroupLink->execute(array('member_id'=>$_POST['uid'],'group_id'=>$_POST['group']));
	} elseif ($checkGroup->rowCount() == 0 && $_POST['add'] == 1) {
		//add
		$insertGroupLink = $main->conn->prepare("INSERT INTO member_group_link (member_id,group_id) VALUES (:member_id,:group_id)");
		$insertGroupLink->execute(array('member_id'=>$_POST['uid'],'group_id'=>$_POST['group']));
	}
?>