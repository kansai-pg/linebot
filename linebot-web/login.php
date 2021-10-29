<?php
session_start();
#https://man.plustar.jp/php/function.openssl-random-pseudo-bytes.html
$bytes = openssl_random_pseudo_bytes(5);
$_SESSION['state'] = bin2hex($bytes);
session_write_close();
$sendurl = "https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=1656389284&redirect_uri=https://linebot-web.japan-is.fun/view.php&state=" . $_SESSION['state'] .  "&scope=profile";
#https://take-lab.com/2017/12/04/post-317/
header('Location: ' . $sendurl, true, 301);
exit;
?>
