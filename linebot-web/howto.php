<?php
include('tpl.php');
$html = <<< EOM

    <div class="container">
        <div class="sdfsdgfsd">
            <!-- https://www.sejuku.net/blog/49377 -->
            <!-- https://getbootstrap.jp/docs/4.3/content/tables/ -->
            <!-- 調べて頑張ったけどどうしても正しく表示できないから学校の先生のコードを見ました。コピペでは無いから許して -->
            <h2> コマンド一覧 </h2>
            <table class="table table-striped table-responsive">
                <tr><th scope="col" nowrap>コマンド</th><th scope="col" nowrap>説明</th></tr>
                    <tr> <td>登録</td><td>部屋を登録する</td> </tr>
                    <tr> <td>日時設定</td><td>通知頻度登録 or 変更</td> </tr>
                    <tr> <td>時刻設定</td><td>通知時刻変更</td> </tr>
                    <tr> <td>登録一覧</td><td>登録部屋一覧表示</td> </tr>
                    <tr> <td>削除</td><td>登録部屋削除</td> </tr>
            </table>
        </div>
    </div>
EOM;

echo $html;  
?>
