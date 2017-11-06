$(function(){
  'use strict';//厳密なエラーチェック

  $('#new_todo').focus();//ページを読み込んだ時#new_todoにfocus()があたるようにする

  // update 状態の更新
  $('#todos').on('click', '.update_todo', function() {
    // update_todoに行う処理
    // idを取得
    var id = $(this).parent('li').data('id');//update_todoの親要素のliのidを（todoのid）取得して代入
    // ajax処理
    $.post('_ajax.php',{
      id: id,
      mode: 'update',//modeは更新なのでupdate
      token: $('#token').val() //tokenの要素のvalueを与える
    }, function(res){//_ajax.phpでの処理が終わるとresというオブジェクトが帰ってくる
      if(res.state === '1'){//stateが1ならばクラスを追加
        $('#todo_' + id).find('.todo_title').addClass('done');
      }else{
        $('#todo_' + id).find('.todo_title').removeClass('done');
      }
    });
  });

  // delete todo削除
  $('#todos').on('click', '.delete_todo', function() {
    // delete_todoに行う処理
    // idを取得
    var id = $(this).parent('li').data('id');//update_todoの親要素のliのidを（todoのid）取得して代入
    // ajax処理
    if(confirm('are you sure?')){
      $.post('_ajax.php',{
        id: id,
        mode: 'delete',//modeは削除なのでdelete
        token: $('#token').val() //tokenの要素のvalueを与える
      }, function(){
        $('#todo_' + id).fadeOut(800);
      });
    }
  });

  // create todoの追加
  $('#new_todo_form').on('submit', function() {
    // create_todoに行う処理
    // titleを取得
    var title = $('#new_todo').val();
    // ajax処理

    $.post('_ajax.php',{
      title: title,
      mode: 'create',//modeは作成なのでcreate
      token: $('#token').val() //tokenの要素のvalueを与える
    }, function(res){//追加の際に新しく挿入したレコードidが必要なのでresを渡す
      //liを追加
      var $li = $('#todo_template').clone();//todo_templateをクローン
      $li//liに対して色々な属性を追加していく
        .attr('id','todo_' + res.id)//id属性に関してtodo_と帰ってきたresオブジェクトのidを合体させたものをつける
        .data('id', res.id)//data-idにもつける
        .find('.todo_title').text(title);//find()で.todo_titleを探して持っている値をtitleにする
      $('#todos').prepend($li.fadeIn());//#todosの中身の一番上にliを追加する。prepend()：先頭に追加する
      $('#new_todo').val('').focus();//#new_todoの中身を空にしてfocus()をあてる
    });
    return false; //submitされて画面遷移すると困るので防ぐ
  });
});
