<?php

session_start();

require_once(__DIR__. '/config.php');
require_once(__DIR__. '/function.php');
require_once(__DIR__. '/Todo.php');

$todoApp = new \MyApp\Todo();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  try{
    $res = $todoApp->post();
    header('Content-Type: application/json');//jsonを扱う宣言
    echo json_encode($res);
    exit;
  }catch(Exception $e){
    header($_SERVER['SERVER_PROTOCOL'] . '500 internal Server Error',true,500);//SERVER_PROTOCOL で HTTP/1.0 や HTTP/1.1 を返しつつ、「 500 Internal Server Error」とする。第 2 引数は replace に関するものですが…、true で OK？
    echo $e->getMessage();
    exit;
  }
}
