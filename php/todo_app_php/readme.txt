最初にmysqlにてデータベースを作成

myspl -u root -p;
パスワード

データベースを作成
create database dotinstall_todo_app;

ユーザーに権限を与える（パスワードaaa）
grant all on dotinstall_todo_app.* to dbuser@localhost identified by 'aaa';

作成したデータベースに切り替える
use dotinstall_todo_app;

テーブルを作成

idは整数でnullなし連番　プライマリーキーに設定
stateはtinyintで0か1を入力。デフォルトで0

create table todos(
  id int not null auto_increment primary key,
  state tinyint(1) default 0, /* 0:not finished, 1:finished */
  title text
);

いくつかデータを入れておく

insert into todos (state,title) values
(0,'todo 0'),
(0,'todo 1'),
(1,'todo 2');

データベースの設計を確認
desc todos;

データベースの確認
select * from todos;

データベースを扱うにあたっての設定やよく使う関数を最初にまとめる

config.php
・データベースの設定

function.php
・文字列をえすけーぷするための関数

htmlで画面を作成
index.php

styleの設定
styles.css作成

Todoクラスを作成
Todo.php作成
index.phpからTodo.phpを呼び出す

Todo.phpにてデータベースの接続

getAll()の作成

余談

<?php echo は <?= に省略することができる
