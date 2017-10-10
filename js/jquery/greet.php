<?php

// echo htmlspecialchars("hi! ".$_GET['name'],ENT_QUOTES,"utf-8");

//複数データを返す時（json使用）

$rs = array(
    "message" => htmlspecialchars("hi! " . $_GET['name'], ENT_QUOTES, "utf-8"),
    "length" => strlen($_GET['name'])
);//複数返す時は配列を作成

header('Content-Type: application/json; charset=utf-8');//アプリケーションタイプをjsonにして文字コードをurfに設定する
echo json_encode($rs);//配列をjson形式にして表示

 ?>
