<?php
session_start();
ini_set('display_errors', "On");
require('dbconnect.php');
$user_id = $_SESSION['user_id'];

$frequency = mb_convert_kana($_POST['data'], "n");
$frequency_date = date("Y-m-d", strtotime("+ " . $frequency . "day"));
#https://gray-code.com/php/make-the-board-vol8/
$room_name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
$time = $_POST['time'];

if(empty($user_id)){
    header("Location: index.html");
}
$back = "";

if(empty($room_name) || preg_match('/^(\s|　)+$/',$room_name)){
    $_SESSION['error_name'] = "←部屋名が入力されていません";
    $back = true;
}
#https://www.flatflag.nir87.com/is_numeric-682
if(!preg_match('/^[1-9]+$/', $frequency)){
    $_SESSION['error_data'] = " ←通知頻度の値が不正です。";
    $back = true;
}
if(!preg_match('/^[0-9]+$/',$time) || $time >= 25){
    $_SESSION['error_time'] = " ←通知時刻の値が不正です。";
    $back = true;
}
// if(empty($_GET['time'])){
//     $_SESSION['error_time'] = "←不正な動作です。";
//     $back = true;
// }
// if(empty($frequency)){
//     $_SESSION['error_data'] = "←頻度が入力されていません" . $frequency;
//     $back = true;
// }

$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if($back){
    header("Location: form.php");
}else{
    if (!empty($_SESSION['id'])) {
        $stmt = $dbh->prepare('update mainid  SET room_name = :room_name, time = :time, frequency = :frequency, datetime = LOCALTIMESTAMP + :frequency WHERE id = :id');
        $stmt->bindValue(":id",$_SESSION['id'],PDO::PARAM_STR);
    } else {
        $stmt = $dbh->prepare("INSERT INTO mainid ( id, user_id, status, room_name, time, frequency, datetime, ON_DUTY) VALUES (SYS_GUID(), :user_id, 0, :room_name, :time, :frequency, LOCALTIMESTAMP + :frequency, 'NOT') ");
        $stmt->bindValue(":user_id",$user_id,PDO::PARAM_STR);
    }
    $stmt->bindValue(":room_name",$room_name,PDO::PARAM_STR);
    $stmt->bindValue(":time",$time,PDO::PARAM_INT);
    $stmt->bindValue(":frequency", $frequency, PDO::PARAM_INT);
    $stmt->execute();

    unset($_SESSION['error_name']);
    unset($_SESSION['error_data']);
    unset($_SESSION['error_time']);
    unset($_SESSION['id']);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>クリーンリマインダー</title>
  
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    p {
    font-size: 130%;
    padding: 3px;
    }

    body {
        background:rgb(198, 255, 255);
        max-width: 100%;
    }

    .center {
    background-size: cover;
    height: 500px;
    padding: 20px;
    position: relative;
    font-size: 18px;
    }
    
  </style>
</head>
<body>
    
    <nav class="navbar navbar-expand-sm navbar-dark bg-info">
        <a class="navbar-brand" href="index.html">クリーンリマインダー</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav4" aria-controls="navbarNav4" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav4">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="howto.html">使い方<span class="sr-only">(current)</span></a> 
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="terms.html">利用規約</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container" style="background: white; padding:10px; margin-top: 10px;">
        <div class="center">
            <h1 style="margin: 10px;">送信完了</h1>
            <div class="form-group row">
                    <label class="col-sm-2 col-form-label">部屋名：</label>
                    <div class="col-sm-10">    
                        <input class="form-control" value="<?php echo $room_name;?>" readonly>
                    </div>
                </div>


                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">時間：</label>
                    <div class="col-sm-10">    
                        <input class="form-control" value="<?php echo $time . "時";?>" readonly>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">通知頻度：</label>
                    <div class="col-sm-10">
                        <!-- https://qiita.com/Yorinton/items/2a3854cd878e310a931f -->
                        <input class="form-control" value="<?php echo $frequency . "日ごと"?>" readonly>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">次回通知日：</label>
                    <div class="col-sm-10">
                        <!-- https://qiita.com/Yorinton/items/2a3854cd878e310a931f -->
                        <input class="form-control" value="<?php echo $frequency_date;?>" readonly>
                    </div>
                </div>

                <button onclick="location.href='view.php'" class="btn btn-primary">一覧表示</button>
            
        </div>
    </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
  <script src="https://code.iconify.design/2/2.0.3/iconify.min.js"></script>
</body>
</html>

<!-- https://gray-code.com/html_css/setting-cant-enterd-text-to-input-element/
https://getbootstrap.jp/docs/4.1/components/forms/ -->
