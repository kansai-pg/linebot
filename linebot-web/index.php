<?php
include('tpl.php');
$html = <<< EOM
<div class="container-fluid">
        <div class="row center">
            <div class="page1">
                <div class="text">
                    <h1>もう掃除を忘れない！</h1>
                    <p>掃除特化型リマインダーボット<br>クリーンリマインダー</p>
                    <!-- https://icon-sets.iconify.design/bi/line/ -->
                    <!-- https://qiita.com/AquaMeria/items/cb0e40e4e5755cdac25f -->
                    <a href="https://lin.ee/jrfA7sv"><img src="https://scdn.line-apps.com/n/line_add_friends/btn/ja.png" alt="友だち追加" height="36" border="0"></a>
                </div>
            </div>
        </div>
        <div class="page2">
            <h3>忘れがちな掃除をLineで通知</h3>
            <p>「いつかやらないと」と思っても掃除は忘れがち<br>
                「きっかけ」を作って掃除を思い出しましょう</p>
        </div>
        <div class="page2">
            <h3>最後に掃除した日も通知</h3>
            <p>中には通知が来ても後回しにする人がいると思います<br>
                なので最後に掃除した日も一緒に通知します。<br>
                最後にいつ掃除したのかを確認できます。
            </p>
        </div>
        <div class="page2">
            <h2> 使ってみよう！</h2>
            <a href="https://lin.ee/jrfA7sv"><img src="https://scdn.line-apps.com/n/line_add_friends/btn/ja.png" alt="友だち追加" height="36" border="0"></a>
        </div>
    </div>
EOM;

echo $html;
?>
