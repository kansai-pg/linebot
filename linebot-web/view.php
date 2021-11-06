<?php
session_start();
ini_set('display_errors', 1);
include('login_class.php');

if (empty($_SESSION['state']) or empty($_SESSION['state'])){
    header("Location: index.html");
    exit;
} elseif (empty($_SESSION['user_id'])){
    $getinfo = new get_from_line($_GET['code']);

    $server_state = $_GET['state'];
    $client_state = $_SESSION['state'];
    
    if ($server_state === $client_state) {
        $_SESSION['user_id'] = $getinfo->get_user_info()->userId;
    }
}

$user_id = $_SESSION['user_id'];

require('./dbconnect.php');

#https://gray-code.com/php/getting-data-by-using-pdo/
$stmt = $dbh->prepare("SELECT id, room_name, time, datetime, frequency FROM mainid WHERE user_id = :user_id");

$stmt->bindValue(":user_id",$user_id,PDO::PARAM_STR);

$stmt->execute();

unset($_SESSION['id']);

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
    
    h2{
        padding: 20px;
        margin: auto;
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

    <div class="container" style="background:white; margin-top: 10px; padding: 10px;">
    <h1 style="margin: 10px;"> 登録通知一覧 </h1>
    <hr>
        <div class="page" style="font-size: 18px;">
            <!-- https://www.sejuku.net/blog/49377 -->
            <!-- https://getbootstrap.jp/docs/4.3/content/tables/ -->
            <!-- https://gray-code.com/html_css/specify-no-break-for-cell/ -->
            <!-- 調べて頑張ったけどどうしても正しく表示できないから学校の先生のコードを見ました。コピペでは無いから許して -->
                <table class="d-flex align-items-center table table-striped table-responsive">
                    <tbody class="mx-auto" style="border: 1px solid lightgray;">
                        <tr><th scope="col">部屋名</th><th scope="col" nowrap>時間</th><th scope="col" nowrap>次回通知日時</th><th scope="col" nowrap>通知頻度</th><th scope="col" nowrap>操作</th><th scope="col" nowrap>削除選択</th></tr>
                        <form action="delete.php" method="get" class="d-flex justify-content-center" id="del">
                            <!-- https://qiita.com/Rys8/items/aad482a4bc3bf823c188 -->
                            <?php
                            while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
                            ?>
                                <tr>
                                    <td><?php echo "$row[ROOM_NAME]"; ?></td>
                                    <td><?php echo "$row[TIME]" . "時"; ?></td>
                                    <td><?php echo date('Y-m-d' , strtotime( "$row[DATETIME]" )); ?></td>
                                    <td><?php echo "$row[FREQUENCY]" . "日ごと"; ?></td>
                                    <td><button type="submit" class="btn btn-primary" formaction="edit.php" value="<?php echo "$row[ID]"?>" name="id">編集</button></td>
                                    
                            <td><input type="checkbox" name="id[]" value="<?php echo "$row[ID]"?>" form="del"></td>
                                </tr>
                            <?php
                            }
                            ?>
				    </tbody>
                </table>

                            <div class="d-flex justify-content-center" >
                                <button type="submit" class="btn btn-danger" form="del" style="margin-right:10px;">選択した項目を削除</button>
                        </form>
                                <button type="button" onclick="location.href='./form.php'" class="btn btn-primary">新規データ登録</button>
				            </div>
                    
                <!-- https://qiita.com/saku__saku/items/1fd16b5209e593e668b0 -->
                <!-- https://qumeru.com/magazine/174 -->
        </div>
    </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</body>
</html>
