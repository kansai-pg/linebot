<?php
session_start();

$user_id = $_SESSION['user_id'];

if(empty($user_id)){
    header("Location: index.php");
    exit();
}
include('tpl.php');
?>
<div class="container" style="background: white; padding:10px; margin-top: 10px;">
    <h1 style="margin: 10px;">通知登録</h1>
    <hr>
    <div style="font-size: 18px;">
        <form class="mx-auto" action="confirm.php" method="POST">
            <div class="form-group">
            <label <?php if(isset($_SESSION['error_name'])){echo "style='color:red;'";}?>>部屋名（必須）<?php if(isset($_SESSION['error_name'])){echo $_SESSION['error_name'];} ?></label>
                <input required class="form-control" type="text" name="name" placeholder="自室">
            </div>
            <label <?php if(isset($_SESSION['error_time'])){echo "style='color:red;'";}?>>通知時刻（必須）<?php if(isset($_SESSION['error_time'])){echo $_SESSION['error_time'];} ?></label><br>
            <select class="form-select" name="time">
                <?php
                for($i=7;$i<=24;$i++){
                    print('<option value="'.$i.'">'.$i.'</option>');
                }
                for($j=1;$j<=6;$j++){
                    print('<option value="'.$j.'">'.$j.'</option>');
                }
                ?>
            </select>

            <div class="form-group">
                <label <?php if(isset($_SESSION['error_data'])){echo "style='color:red;'";}?>>通知頻度：（必須） <br> 何日ごとに通知するか数値のみで入力してください（一週間ごとなら「7」) <?php if(isset($_SESSION['error_data'])){echo $_SESSION['error_data'];} ?></label>
                <input required class="form-control" type="tel" name="data">
            </div>
            <div class="d-flex align-items-center">
                <button type="submit" class="btn btn-primary mx-auto" style="width: 50%; margin: 20px;">送信</button>
                <button type="button" class="btn btn-primary" onclick="location.href='view.php'">一覧表示へ戻る</button>
            </div>
        </form>

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
