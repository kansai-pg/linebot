<?php
$basehtml = <<< EOM

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>クリーンリマインダー</title>

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>

    body {
        background:rgb(198, 255, 255);
        max-width: 100%;
    }

    .center {
    background: linear-gradient(rgba(123, 236, 253, 0.9), rgba(123, 236, 253, 0.9)), url("publicdomainq-0021215cvywfh.png");
    background-size: cover;
    height: 500px;
    padding: 20px;
    position: relative;
    }

    .page1{
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        margin: auto;
        width: 100%;
        height: 150px;
        text-align: center;
    }

    .page2{
        font-size: 130%;
        padding: 50px;
        margin: auto;
        text-align: center;
        line-height: 250%;
    }

    h2{
        padding: 20px;
        margin: auto;
    }

    .terms {
       margin: 20px;
       padding: 15px;
    }

  </style>
</head>
<body>

    <nav class="navbar navbar-expand navbar-dark bg-info">
        <a class="navbar-brand" href="index.php">クリーンリマインダー</a>
        <ul class="nav navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="howto.php">使い方</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="terms.php">利用規約</a>
            </li>
        </ul>
    </nav>


</body>
</html>

<!-- https://www.tagindex.com/stylesheet/text_font/font_size.html -->
<!-- https://kiyaku.jp/hinagata/sns.html-->
<!-- https://codezine.jp/article/detail/8072 -->
<!-- http://open.shonan.bunkyo.ac.jp/~ohtan/kouza/css-margin.html -->
<!-- https://miyacle.com/2020/05/bootstrap%E3%82%92%E4%BD%BF%E3%81%A3%E3%81%A6%E3%83%98%E3%83%83%E3%83%80%E3%83%BC%E3%81%A8%E3%83%95%E3%83%83%E3%82%BF%E3%83%BC%E3%82%92%E4%BD%9C%E3%81%A3%E3%81%9F%E8%A9%B1/ -->

EOM;
echo $basehtml;
?>
