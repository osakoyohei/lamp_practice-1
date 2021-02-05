<!DOCTYPE html>
<html lang="ja">
<head>
  <?php include VIEW_PATH . 'templates/head.php'; ?>
  <title>商品購入履歴</title>
  <link rel="stylesheet" href="<?php print h(STYLESHEET_PATH . 'admin.css'); ?>">
</head>
<body>
    <?php include VIEW_PATH . 'templates/header_logined.php'; ?>
    <h1>購入履歴</h1>

    <div class="container">

    <?php include VIEW_PATH . 'templates/messages.php'; ?>
    
    <!--ユーザー毎の購入履歴-->
    <?php if(count($orders) > 0){ ?>
      <table class="table table-bordered">
        <thead class="thead-light">
          <tr>
            <th>注文番号</th>
            <th>購入日時</th>
            <th>該当の注文の合計金額</th>
            <th>購入明細画面</tr>
          </tr>
        </thead>
        <tbody>
          <?php foreach($orders as $order){ ?>
          <tr>
            <td><?php print h($order['id']); ?></td>
            <td><?php print h($order['created_at']); ?></td>
            <td><?php print h(number_format($order['total'])); ?>円</td>
            <td>
                <form method="get" action="order_details.php">
                    <input type="hidden" name="id" value="<?php print h($order['id']); ?>">
                    <input type="submit" value="購入明細表示">
                </form>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } else { ?>
      <p>購入履歴がありません。</p>
    <?php } ?> 
  </div>
</body>
</html>