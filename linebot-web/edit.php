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

include('tpl.php');
?>
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
                    print('<option value="'.$i);
                    if ($room_datas[0]["TIME"] == $j) {
                        print('" selected>'.$j . '</option>');
                    } else {
                        print('">'.$j . '</option>');
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
