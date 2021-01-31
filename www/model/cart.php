<?php 
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'db.php';

//user_idからカート情報を取得する関数
function get_user_carts($db, $user_id){
  $sql = "
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = ?
  ";
  return fetch_all_query($db, $sql, [$user_id]);
}

//user_id.item_idを取得する関数
function get_user_cart($db, $user_id, $item_id){
  $sql = "
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = ?
    AND
      items.item_id = ?
  ";

  return fetch_query($db, $sql, [$user_id, $item_id]);

}

//カートに商品を追加する関数
function add_cart($db, $user_id, $item_id ) {
  $cart = get_user_cart($db, $user_id, $item_id);
  if($cart === false){
    return insert_cart($db, $user_id, $item_id);
  }
  return update_cart_amount($db, $cart['cart_id'], $cart['amount'] + 1);
}

//カートに追加する関数
function insert_cart($db, $user_id, $item_id, $amount = 1){
  $sql = "
    INSERT INTO
      carts(
        item_id,
        user_id,
        amount
      )
    VALUES (?, ?, ?)
  ";

  return execute_query($db, $sql, [$item_id, $user_id, $amount]);
}

//購入数を変更する関数
function update_cart_amount($db, $cart_id, $amount){
  $sql = "
    UPDATE
      carts
    SET
      amount = ?
    WHERE
      cart_id = ?
    LIMIT 1
  ";

  return execute_query($db, $sql, [$amount, $cart_id]);
  
}

//カート商品を削除する関数
function delete_cart($db, $cart_id){
  $sql = "
    DELETE FROM
      carts
    WHERE
      cart_id = ?
    LIMIT 1
  ";

  return execute_query($db, $sql, [$cart_id]);
}

//購入後items.stockからcarts.amountを引き、カート商品を削除する関数
function purchase_carts($db, $carts){
  if(validate_cart_purchase($carts) === false){
    return false;
  }
  foreach($carts as $cart){
    if(update_item_stock(
        $db, 
        $cart['item_id'], 
        $cart['stock'] - $cart['amount']
      ) === false){
        return false;
    }
  }
  
  if(delete_user_carts($db, $carts[0]['user_id']) === false) {
    return false;
  }
}

//カート商品を削除する関数
function delete_user_carts($db, $user_id){
  $sql = "
    DELETE FROM
      carts
    WHERE
      user_id = ?
  ";

  return execute_query($db, $sql, [$user_id]);
}

//合計金額($total_price)関数
function sum_carts($carts){
  $total_price = 0;
  foreach($carts as $cart){
    $total_price += $cart['price'] * $cart['amount'];
  }
  return $total_price;
}

//購入後エラー関数
function validate_cart_purchase($carts){
  if(count($carts) === 0){
    set_error('カートに商品が入っていません。');
    return false;
  }
  foreach($carts as $cart){
    if(is_open($cart) === false){
      set_error($cart['name'] . 'は現在購入できません。');
    }
    if($cart['stock'] - $cart['amount'] < 0){
      set_error($cart['name'] . 'は在庫が足りません。購入可能数:' . $cart['stock']);
    }
  }
  if(has_error() === true){
    return false;
  }
  return true;
}


//課題2・追加テーブルへのデータ保存

//購入履歴登録の関数
function insert_orders($db, $user_id){
$sql = "
    INSERT INTO
      orders(
        user_id
      )
      VALUES(?)
    ";
    
    return execute_query($db, $sql, [$user_id]);
    
}

//購入明細登録の関数
function insert_order_details($db, $name, $price, $amount, $id){
  $sql = "
    INSERT INTO
      order_details(
        item_name,
        price,
        amount,
        order_id
      )
        VALUES(?, ?, ?, ?)
      ";

      return execute_query($db, $sql, [$name, $price, $amount, $id]);
}

//購入履歴、購入明細登録の関数
function purchase_history($db, $carts){
  if (insert_orders($db, $carts[0]['user_id']) === false) {
    return false;
  }
  
  $id = $db->lastInsertId();

  foreach($carts as $cart){
    if(insert_order_details(
      $db,
      $cart['name'],
      $cart['price'],
      $cart['amount'],
      $id
    ) === false){
      return false;
    }
  }
  return true;
}