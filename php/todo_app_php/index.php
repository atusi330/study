<?php

session_start();

require_once(__DIR__. '/config.php');
require_once(__DIR__. '/function.php');
require_once(__DIR__. '/Todo.php');

// get todos Todoを所得した後の処理
$todoApp = new \MyApp\Todo();//インスタンスの生成
$todos = $todoApp->getAll();//すべてのTodoを取得する

// var_dump($todos);//とりあえず出力の確認
// exit;

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>My Todos</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div id="container">
    <h1>Todos</h1>
    <!-- 新規作成のためのフォーム -->
    <form action="" id="new_todo_form"><!-- Ajaxを使うのでactiionは空 -->
      <input type="text" id="new_todo" placeholder="what needs to be done?"><!-- placeholderはデフォルトで表示するものを入れるvalueとは少し違う -->
    </form>
    <ul id="todos">
      <?php foreach($todos as $todo) : ?>
        <li id="todo_<?= h($todo->id); ?>" data-id="<?= h($todo->id); ?>">
          <input type="checkbox" class="update_todo" <?php if($todo->state === '1'){echo 'checked';} ?>>
          <span class="todo_title <?php if($todo->state === '1'){echo 'done';}?>"><?= h($todo->title); ?></span>
          <div class="delete_todo">×</div>
        </li>
    <?php endforeach; ?>
    <li id="todo_template" data-id="">
      <input type="checkbox" class="update_todo" >
      <span class="todo_title"></span>
      <div class="delete_todo">×</div>
    </li>
    </ul>
  </div>
  <input type="hidden" id="token" value="<?= h($_SESSION['token']); ?>"><!-- tokenの埋め込み -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <script src="todo.js"></script>
</body>
</html>
