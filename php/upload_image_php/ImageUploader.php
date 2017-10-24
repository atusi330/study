<?php

namespace MyApp;

class ImageUploader{

  private $_imageFileName;
  private $_imageType;

  public function upload(){
    try{
      //error check アップロードしときにエラーがないか？
      $this->_validateUpload();
      //type check 画像を保存する際に拡張子の種類によってPHPの命令が変わるので画像のタイプをチェック
      $ext = $this->_validateImageType();//ファイルの拡張子を格納するextension

      //save 上記ができたらそれを保存
      $savePath = $this->_save($ext);//保存の時に拡張子が必要となってくるので$extを引数として渡す

      //create thunbnail 必要であればサムネイルを作成
      $this->_createThumbnail($savePath);

      // var_dump($ext);//出力確認
      // exit;

      $_SESSION['success'] = 'Upload Done!';//エラーがなければsuccessにメッセージが入る
    }catch(\Exception $e){
    $_SESSION['error'] = $e->getMessage();//エラーがあればエラーメッセージを代入
    exit;
  }
  // redirect
      header('Location: http://' . $_SERVER['HTTP_HOST']);//index.phpに飛ばす
      exit;
    }

    public function getResults(){
      $success = null;
      $error = null;
      if(isset($_SESSION['success'])){
        $success = $_SESSION['success'];
        unset($_SESSION['success']);//代入したら消しておかないと後でまた出てくる
      }

      if(isset($_SESSION['error'])){
        $success = $_SESSION['error'];
        unset($_SESSION['error']);
      }
      return [$success, $error];
    }

    public function getImages() {
      $images = [];//最終的に返す値
      $files = [];//ファイル名でソートをかけるための配列
      //ここからほぼ決まり文句（あるフォルダの中にあるファイルを精査していきたい時に次のように書く）
      $imageDir = opendir(IMAGES_DIR);//$imageDirに画像フォルダの中身を全て代入する
      while (false !== ($file = readdir($imageDir))){//$imageDirを一つずつ読み込んでいき、読み込めるものがなくなれば、ループを終了する
        if($file === '.' || $file === '..'){//$fileの中に入ってくるものの中にカレントディレクトリを表す'.'もしくは親ディレクトリを表す'..'があるがそれは必要ないのでループを飛ばすためにcontinue;としておく
          continue;
        }
        $files[] = $file;//$files[]に$fileを格納してあとでソートできるようにしておく
        //サムネイルがあるかどうかで$imagesに渡すものが変わるのでその処理
        if(file_exists(THUMBNAIL_DIR . '/' . $file)){//サムネイルのフォルダに同じ名前のファイルがあるかどうか
          $images[] = basename(THUMBNAIL_DIR) . '/' . $file;//あればサムネイルを$imageに代入
        }else{
          $images[] = basename(IMAGES_DIR) . '/' . $file;//なければアップロードされた画像を$imageに代入
      }
    }
    array_multisort($files, SORT_DESC, $images);//ソート（$files順に逆向きオプションで$imagesを並べ替える）わかりにくいが、要はfiles[0]には一番古い画像が入っているはずなので、images[0]から順に新しいファイルが入るように並べ替えている
    return $images;
    }

    private function _createThumbnail($savePath) {
      $imageSize = getimagesize($savePath);//getimagesize()でファイルサイズを取得
      $width = $imageSize[0];
      $height = $imageSize[1];
      if($width > THUMBNAIL_WIDTH) {//画像が規定サイズよりも大きい場合サムネを作成
        $this->_createThumbnailMain($savePath,$width,$height);//処理が多いのでメソッド(_createThumbnailMain)を作成
      }
    }
    //サムネを作る基本的な考え方
    //元画像の画像リソーズを作ってをれを元にサムネイルを作成
    private function _createThumbnailMain($savePath,$width,$height) {
      //画像リソースの作成（拡張子によって変わる）
      switch ($this->_imageType) {
        case IMAGETYPE_GIF:
          $srcImage = imagecreatefromgif($savePath);//imagecreatefrom**で画像リソースを作成する
          break;
        case IMAGETYPE_JPEG:
          $srcImage = imagecreatefromjpeg($savePath);
          break;
        case IMAGETYPE_PNG:
          $srcImage = imagecreatefrompng($savePath);
          break;
      }
      //サムネイルの高さは画像の高さ×サムネイルの横幅÷画像の横幅
      $thumbHeight = round($height * THUMBNAIL_WIDTH / $width);
      ;//サムネイルの元イメージ（絵で言うとキャンパスみたいなもの？）をimagecreatetruecolor(サムネイルの幅,サムネイルの高さ)作成
      $thumbImage = imagecreatetruecolor(THUMBNAIL_WIDTH,$thumbHeight);
      //作成した元イメージに元イメージの情報を渡す（コピーする）。
      //imagecopyresampled(コピー先のリソース,コピー元のリソース,コピー先のx座標,コピー先のy座標,コピー元のx座標,コピー元のy座標,コピー先の幅,コピー先の高さ,コピー元の幅,コピー元の高さ);
      imagecopyresampled($thumbImage,$srcImage,0,0,0,0,THUMBNAIL_WIDTH,
      $thumbHeight,$width,$height);

    //作成したサムネイルの保存（拡張子ごとに異なる）
    switch ($this->_imageType) {
      case IMAGETYPE_GIF:
        //image**(画像の情報,保存場所);
        imagegif($thumbImage, THUMBNAIL_DIR . '/' .$this->_imageFileName);
        break;
      case IMAGETYPE_JPEG:
        imagejpeg($thumbImage, THUMBNAIL_DIR . '/' .$this->_imageFileName);
        break;
      case IMAGETYPE_PNG:
        imagepng($thumbImage, THUMBNAIL_DIR . '/' .$this->_imageFileName);
        break;
    }
  }

    private function _save($ext) {
      //保存する時のファイル名を作成
      //他のものと重複せずあとでソードができるようにする
      $this->_imageFileName = sprintf(
        '%s_%s.%s',
        time(), //現在までの経過ミリ秒
        sha1(uniqid(mt_rand(), true)), //ランダムな文字列で重複しないもの（やり方色々）
        $ext //拡張子
      );
      $savePath = IMAGES_DIR . '/' . $this->_imageFileName;//保存するためのパスを作成
      $res = move_uploaded_file($_FILES['image']['tmp_name'],$savePath);//move_uploaded_file()を行うとtmpフォルダに入っていたファイルを$savePathに動かすことができる。移動が正しく行われるとresにtrueが入る
      if($res === false){//移動が失敗するとfalseが帰ってくるのでその場合エラーを吐く
        throw new \Exception('Could not upload!');
      }
      return $savePath;//サムネイル作成に必要な$savePathを返す
    }

    //exif_imagetype()にファイルを渡すと拡張子を返してくれる
    private function _validateImageType() {
      $this->_imageType = exif_imagetype($_FILES['image']['tmp_name']);
      switch($this->_imageType){
        case IMAGETYPE_GIF:
          return 'gif';
        case IMAGETYPE_JPEG:
          return 'jpg';
        case IMAGETYPE_PNG:
          return 'png';
        default:
          throw new \Exception('PNG/JPEG/GIF only!');
      }
    }

    private function _validateUpload() {
      // var_dump($_FILES); //$_FILESに格納された中身を確認
      // exit;

      //ファイルサイズが大きすぎて画像がセットされないケース(imageがセットされているか？)や改ざんされたフォームから変なデータがとんできていないか(必ずセットされているimageとerrorが入っているか？)のチェックを行う
      if(!isset($_FILES['image']) || !isset($_FILES['image']['error'])){
        throw new \Exception('Upload Error!');
      }

      switch ($_FILES['image']['error']) {
        case UPLOAD_ERR_OK:
          return true;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          throw new \Exception('File too large!');
        default:
          throw new \Exception('Err: '. $_FILES['image']['error']);
          break;
      }
    }
}

 ?>
