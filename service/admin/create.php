<?php

/*
|--------------------------------------------------------------------------
| 添加新主题，并广播给所有人
|--------------------------------------------------------------------------
|
| 输入
| - user::post([
|     'color',
|     'title',
|     'description'，
|     'prefix',
|     'tag',
|     'freeze_time',
|     'reopen_time',
|     'reopen_description'
|   ])
|
|
*/

  $input = user::post('all');
  $wx = new angel\wechat($GLOBALS['wechat_config']['appid'], $GLOBALS['wechat_config']['secret'], $GLOBALS['wechat_config']['token']);
  $access_token = $wx->access_token();
  session_start();
  if (!isset($_SESSION['openid'])) {
      // 如果未登录
      js::alert('用户未登录');
      jump::back(-1);
      exit();
  } elseif (!is::in($_SESSION['openid'], $GLOBALS['admin'])) {
      // 如果不是管理员
      js::alert('不是管理员');
      jump::back(-1);
      exit();
  } else {
      foreach ($input as $v) {
          if (is::empty($v)) {
              // 如果有项目为空
              js::alert('有项目为空，请填写完整。请保存重要数据并刷新页面');
              jump::back(-1);
              exit();
          }
      }
  }

  $latest = sql::select('story')->order('id')->by('desc')->limit(1)->fetch()['id']+1;
  sql::insert('stories')->this([
    $latest,
    $input['title'],
    $input['prefix'],
    $input['color'],
    $input['description'],
    $input['tag'],
    0,
    $input['freeze_time'],
    $input['reopen_time'],
    $input['reopen_description'],
    0,0,0,0
  ]);

  foreach ($GLOBALS['admin'] as $admin_id) {
      $wx->tmp_return($access_token, [
        'to' => $admin_id,
        'id' => '-hIHETId6A5yDSJW1jjOd3V4hI_MzCiDTfea9Q1HdWE',
        'url' => user::url().'/story/tran/fetch_openid/'.$admin_id.'/story+'.$latest,
        'data' => [
          'first' => [
              'value' => '有新冰棒生成，如满意请点击激活 📝'
            ],
            'keyword1' => [
              'value' => user::post('title'),
              'color' => '#808080'
            ],
            'keyword2' => [
              'value' => $latest,
              'color' => '#808080'
            ],
            'keyword3' => [
              'value' => date('H:i'),
              'color' => '#808080'
            ],
            'remark' => [
              'value' => user::post('description'),
              'color' => '#808080'
            ]
        ]
    ]);
  }

  js::alert('冰棒生成成功');
  jump::to(user::url().'/story/'.$latest);
