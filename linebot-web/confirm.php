<?php
session_start();
ini_set('display_errors', "On");
require('./dbconnect.php');
#ユーザーID
$user_id = $_SESSION['user_id'];
#全角で入力されても半角に変換する
$frequency = mb_convert_kana($_POST['data'], "n");
#今日の日付に入力された日数を足す
$frequency_date = date("Y-m-d", strtotime("+ " . $frequency . "day"));
#https://gray-code.com/php/make-the-board-vol8/
#ここは文字を受け付けるのでHTMLインジェクション対策
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
#空文字やスペースのみ、数字以外の文字が混じっているとエラーを返す
if(!preg_match('/^[1-9]+$/', $frequency)){
    $_SESSION['error_data'] = " ←通知頻度の値が不正です。";
    $back = true;
}
#数値が指定の範囲で無ければエラーを返す
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
}else{#$_SESSION['id']にデータがあれば編集用としてデータを処理する。なければ新規登録としてデータを処理する
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

include('tpl.php');
?>
<div class="container" style="background: white; padding:10px; margin-top: 10px;">
    <div class="center" style="font-size: 18px; background-image:none;">
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

            <button type="button" class="btn btn-primary" onclick="location.href='view.php'">一覧表示へ戻る</button>
    </div>
</div>
</body>
</html>

<!-- https://gray-code.com/html_css/setting-cant-enterd-text-to-input-element/
https://getbootstrap.jp/docs/4.1/components/forms/ -->
