<?php
ini_set('display_errors', "On");
require('./dbconnect.php');

$stmt = $dbh->prepare("DELETE FROM mainid WHERE id = :id");
#削除対象のデータを配列で受け取り削除する
foreach ($_GET["id"] as $value) {
	#$stmt = $dbh->prepare("DELETE FROM mainid WHERE id = :id");
	$stmt->bindValue(":id",$value,PDO::PARAM_STR);
	$stmt->execute();
}

header("Location: view.php");
exit();

?>
