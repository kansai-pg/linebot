<?php
// https://お役立ち.xyz/php/require/7012/
try{
    #https://qiita.com/shin1x1/items/68732dcf02a93c0a0fbb

    $db = "oci:dbname=db202110141010_high;charset=utf8";
    $dbh = new PDO($db, 'admin', getenv('pass'));
} catch (PDOException $e) {
    echo 'DB接続エラー！';
  }
?>
