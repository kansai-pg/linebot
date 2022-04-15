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

include('tpl.php');
?>

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

</body>
</html>
