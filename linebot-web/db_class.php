<?php
ini_set('display_errors', 1);

class db {

    public function db_connect() {
      try{
        $dbh = new PDO("oci:dbname=db202110141010_high;charset=utf8", 'admin', getenv('pass'));
      } catch (PDOException $e) {
        echo 'DB接続エラー！';
      }
      return $dbh;
    }
}
?>
