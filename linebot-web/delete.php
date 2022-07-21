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
#print_r($_GET["id"]);
echo "<h1> 削除完了 </h1>";

header("Location: view.php");
exit();

?>
