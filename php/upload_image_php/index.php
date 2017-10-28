<?php

session_start();//セッションを使ってアップロード関係のメッセージをやりとりする

ini_set('display_errors', 1);//エラーメッセージをブラウザに表示させる
define('MAX_FILE_SIZE', 1 * 1024 * 1024); // 1MB
define('THUMBNAIL_WIDTH', 400);//サムネイルを作る際の閾値
define('IMAGES_DIR', __DIR__ . '/images');//画像を入れて置く場所
define('THUMBNAIL_DIR', __DIR__ . '/thumbs');//サムネを入れて置く場所

//画像処理の際にGDというプラグインを使用するのでそれがあるかどうか確認
if (!function_exists('imagecreatetruecolor')) {
  echo 'GD not installed';
  exit;
}

function h($s) {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

//ファイルの読み込み
require 'ImageUploader.php';

$uploader = new \MyApp\ImageUploader();

//REQUEST_METHODがPOSTならば、フォームにポスト（画像が入った？）されたということになる
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $uploader->upload();
}

//アップロード後のメッセージを格納する配列
list($success,$error) = $uploader->getResults();//配列でうまく行った時のメッセージとエラーメッセージをlistで一気に受け取る

//表示する画像を格納する配列
$images = $uploader->getImages();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Image Uploader</title>
  <style>
  body {
    text-align: center;
    font-family: Arial, sans-serif;
  }
  ul{
    list-style: none;
    margin: 0;
    padding: 0;
  }
  li{
    margin-bottom: 5px;
  }
input[type=file]{
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  cursor: pointer;
  opacity: 0;
}

.btn{
  position: relative;
  display: inline-block;
  width: 300px;
  padding: 7px;
  border-radius: 5px;
  margin: 10px auto 20px;
  color: #fff;
  box-shadow: 0 4px #0088cc;
  background: #00aaff;
}

.btn:hover{
  opacity: 0.8;
}

.msg {
  margin: 0 auto 15px;
  width: 400px;
  font-weight: bold;
}

.msg.success{
  color:#4caf50;
}

.msg.error{
  color:#f44336;
}


  </style>
</head>
<body>
<div class="btn">
  Upload!
  <form action="" method="post" enctype="multipart/form-data" id="my_form">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo h(MAX_FILE_SIZE); ?>">
    <input type="file" name="image" id="my_file">
  </form>
</div>
<!-- アップロード関係のメッセージ表示　jQeryで3秒後に消す -->
<?php if(isset($success)) : ?>
  <div class="msg success"><?php echo h($success); ?></div>
<?php endif; ?>
<?php if(isset($error)) : ?>
  <div class="msg error"><?php echo h($error); ?></div>
<?php endif; ?>

  <ul>
    <?php foreach($images as $image) : ?>
      <li>
        <a href= "<?php echo h(basename(IMAGES_DIR)). '/' . h(basename($image)); ?>">
          <img src="<?php echo h($image); ?>"
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>
$(function(){
  $('.msg').fadeOut(3000);
  $('#my_file').on('change',function(){
    $('#my_form').submit();
  })
})
</script>
</body>
</html>
