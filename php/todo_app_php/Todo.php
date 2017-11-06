<?php

//CSRF対策
//token発行してsessionに格納
//フォームからもtokenを発行、送信


namespace MyApp;

class Todo{
  private $_db;

  public function __construct(){//コンストラクタでdbへの接続を行う
    //tokenの作成
    $this->_createtoken();
    //dbへの接続は例外が発生する場合があるのでtryで囲う
    try {
      $this->_db = new \PDO(DSN, DB_USERNAME, DB_PASSWORD);//作成した$_dbに関してPDOのインスタンスを作成する
      $this->_db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);//データベースの例外の扱いについてAttributeの設定を行う
    }catch (\PDOException $e){
      echo $e->getMessage();
      exit;
    }
  }

  private function _createtoken(){
    if(!isset($_SESSION['token'])){
      $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(16));//32桁の推測されにくい文字列を作成。ほぼ決まり文句
    }
  }

  public function getAll(){
    //stmtオブジェクトを作成して値を全件抽出（query()）
    //order by desc とするとこで常に最新のものが上くるようにしている
    $stmt = $this->_db->query("select * from todos order by id desc");
    //抽出した結果をオブジェクト形式で返す
    return $stmt->fetchAll(\PDO::FETCH_OBJ);
  }

  public function post(){
    $this->_validateToken();//_validateTokenの呼び出し
    //modeが必ず渡されているはずなのであるか確認
    if(!isset($_POST['mode'])){
      throw new \Exception('mode not set !');
    }
    //返されたmodeに対してそれぞれ処理をおこなう
    switch($_POST['mode']){
      case 'update':
      return $this->_update();//メソッドの返り値は配列
      //returnしているのでブレイクは必要ない
      case 'create':
      return $this->_create();
      case 'delete':
      return $this->_delete();
    }
  }

//tokenのチェック(CSRF対策)
  private function _validateToken() {
    if(
      !isset($_SESSION['token']) ||//sessionにtokenがセットされているか？
      !isset($_POST['token']) ||//postにtokenがセットされているか？
      $_SESSION['token'] !== $_POST['token']//セットされているtokenが同じものか？
    ){
      throw new \Exception('invalid token!');
    }
  }

//stateの更新
  private function _update() {
    if (!isset($_POST['id'])) {
        throw new \Exception('[update] id not set!');
      }

      //確実に更新されたtodoの$stateが取得できるようにトランザクションで囲む
      //同時アクセス時にidがずれることを防ぐため
      $this->_db->beginTransaction();

      //stateの更新
      //データベースの更新を行うsql文を変数に入れる
      //プレースホルダー(?)を使用しても良いが、プレースホルダーは文字列の時に使われる。今回はint型なのでsprintf()の%dを用いている
      $sql = sprintf("update todos set state = (state + 1) %% 2 where id = %d",$_POST['id']);//(state + 1) % 2 は0か1を入れたい時に使うちょっとしたテクニック。また変数の埋め込みを行う場合%一つだとエラーになるので%%と書く
      $stmt = $this->_db->prepare($sql);//データベースの更新準備
      $stmt->execute();//更新の実行

      //更新されたstateを返す
      $sql = sprintf("select state from todos where id = %d",$_POST['id']);
      $stmt = $this->_db->query($sql);//取得
      $state = $stmt->fetchColumn();//fetchColomn 結果セットの次行から単一カラムを返す

      $this->_db->commit();
      //トランザクション終了

      //配列で返すきまりごとにしたのでこうする
      return[
        'state' => $state//state配列に$stateの値を代入
      ];
    }
    private function _create(){
      if (!isset($_POST['title']) || $_POST['title'] === '') {
          throw new \Exception('[create] title not set!');
        }

        //データベースの削除
        //データベースの削除を行うsql文を変数に入れる
        $sql = "insert into todos (title) values (:title)";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute([':title' => $_POST['title']]);

        //配列で返すきまりごとにしたのでこうする
        return[
          'id' => $this->_db->lastInsertId()
        ];
    }
    private function _delete(){
      if (!isset($_POST['id'])) {
          throw new \Exception('[delete] id not set!');
        }

        //データベースの削除
        //データベースの削除を行うsql文を変数に入れる
        $sql = sprintf("delete from todos where id = %d",$_POST['id']);
        $stmt = $this->_db->prepare($sql);
        $stmt->execute();

        //配列で返すきまりごとにしたのでこうする
        return[];
    }

  }


 ?>
