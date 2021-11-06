<?php
session_start();
ini_set('display_errors', "On");

if (empty($_GET['id'])){
    header("Location: index.html");
    exit;
}

#削除対象のデータの主キー (UUIDなのでこの方法で受け取る)
$id = $_GET['id'];

$_SESSION['id'] = $id;

require('./dbconnect.php');

#https://gray-code.com/php/getting-data-by-using-pdo/
$stmt = $dbh->prepare("SELECT id, room_name, time, datetime, frequency FROM mainid WHERE id = :id");

$stmt->bindValue(":id",$id,PDO::PARAM_STR);

$stmt->execute();

$room_datas = $stmt->fetchAll();

#print_r($room_datas);
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
        <h1 style="margin: 10px;">通知編集</h1>
        <hr>
        <div style="font-size: 18px;">
            <form class="mx-auto" action="confirm.php" method="POST">
                <div class="form-group">
                <label <?php if(isset($_SESSION['error_name'])){echo "style='color:red;'";}?>>部屋名<?php if(isset($_SESSION['error_name'])){echo $_SESSION['error_name'];} ?></label>
                    <input required class="form-control" type="text" name="name" value="<?php echo $room_datas[0]["ROOM_NAME"]; ?>" >
                </div>
                <label <?php if(isset($_SESSION['error_time'])){echo "style='color:red;'";}?>>通知時刻（必須）<?php if(isset($_SESSION['error_time'])){echo $_SESSION['error_time'];} ?></label><br>
                <select class="form-select" name="time">
                    <?php
                    for($i=7;$i<=24;$i++){
                        print('<option value="'.$i);
                        if ($room_datas[0]["TIME"] == $i) {
                            print('" selected>'.$i . '</option>');
                        } else {
                            print('">'.$i . '</option>');
                        }
                    }
                    for($j=1;$j<=6;$j++){
                        print('<option value="'.$j.'">'.$j);
                        if ($room_datas[0]["TIME"] == $i) {
                            print('" selected>'.$i . '</option>');
                        } else {
                            print('">'.$i . '</option>');
                        }
                    }
                    ?>
                </select>

                <div class="form-group">
                    <label <?php if(isset($_SESSION['error_data'])){echo "style='color:red;'";}?>>通知頻度：（必須） <br> 何日ごとに通知するか数値のみで入力してください（一週間ごとなら「7」) <?php if(isset($_SESSION['error_data'])){echo $_SESSION['error_data'];} ?></label>
                    <input required class="form-control" type="tel" name="data" value="<?php echo $room_datas[0]["FREQUENCY"]; ?>">
                </div>
                <div class="d-flex align-items-center">
                    <button type="submit" class="btn btn-primary mx-auto" style="width: 50%; margin: 20px;">送信</button>
                    <button type="button" class="btn btn-primary" onclick="location.href='view.php'">一覧表示へ戻る</button>
            </form>
                </div>
        </div>
    </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
  <script src="https://code.iconify.design/2/2.0.3/iconify.min.js"></script>
</body>
</html>

<!-- https://www.sejuku.net/blog/78997
https://www.tagindex.com/stylesheet/form/width_height.html
https://www.homepage-tukurikata.com/html/a-href.html
https://weback.net/htmlcss/1284/
https://www.tagindex.com/html5/form/input_reset.html
https://techacademy.jp/magazine/40506 
http://www.htmq.com/html5/input_required.shtml
https://www.tagindex.com/html5/form/input_email.html -->
