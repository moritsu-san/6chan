<?php
// データベースの接続情報
define( 'DSH', 'mysql:host=localhost;dbname=board;charset=utf8');
define( 'DB_USER', 'root');
define( 'DB_PASS', 'password');

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// 変数の初期化
$now_date = null;
$data = null;
$message = array();
$message_array = array();
$error_message = array();
$clean = array();
$dbh = null;
$sql = null;
$stmt = null;

session_start();

if( !empty($_POST['btn_submit']) ) {

  // 表示名の入力チェック
	if( empty($_POST['view_name']) ) {
		$error_message[] = 'ハンドルネームを入力してください。';
	} else {
		$clean['view_name'] = htmlspecialchars( $_POST['view_name'], ENT_QUOTES);
    $clean['view_name'] = preg_replace( '/\\r\\n|\\n|\\r/', '', $clean['view_name']);

		// セッションに表示名を保存
		$_SESSION['view_name'] = $clean['view_name'];
	}

	// メッセージの入力チェック
	if( empty($_POST['message']) ) {
		$error_message[] = 'メッセージを入力してください。';
		$clean['message'] = htmlspecialchars( $_POST['message'], ENT_QUOTES);
	} else {
		$clean['message'] = htmlspecialchars( $_POST['message'], ENT_QUOTES);
	}

  if( empty($error_message) ) {
    // データベースに接続
    try {
      $dbh = new PDO( DSH, DB_USER, DB_PASS, [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_EMULATE_PREPARES => false,
        ]);
            
      // 書き込み日時を取得
			$now_date = date("Y-m-d H:i:s");
					
			// データを登録するSQL作成
			$sql = "INSERT INTO message (view_name, message, post_date) VALUES ( '$clean[view_name]', '$clean[message]', '$now_date')";
						
			// データを登録
			$stmt = $dbh->query($sql);
					
			if( $stmt ) {
				$_SESSION['success_message'] = '投稿を完了しやした♪';
			} else {
				$error_message[] = '投稿に失敗しました。';
			}

			header('Location: ./');
			exit();

    } catch(PDOException $e) {
        $error_message[] = 'データベースとの接続に失敗しました。' . $e->getMessage();
    }
  }
}

// データベースに接続
try {
   $dbh = new PDO( DSH, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);

  $sql = "SELECT view_name,message,post_date FROM message ORDER BY post_date DESC";
	$stmt = $dbh->query($sql);
	
	if( $stmt ) {
		$message_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
    
} catch(PDOException $e) {
  $error_message[] = 'データベースとの接続に失敗しました。' . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="stylesheet.css">
<title>6ちゃん掲示板（仮）</title>
</head>

<body>
<h1>6ちゃん掲示板（仮）</h1>
<p class="explain">(管理人：もりつ)<br>ひろゆきのまねして掲示板つくりやした～♫<br>便所の落書きとして利用してくれたら幸いや～す(^^♪</p>

<?php if( empty($_POST['btn_submit']) && !empty($_SESSION['success_message']) ): ?>
    <p class="success_message"><?php echo $_SESSION['success_message']; ?></p>
		<?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if( !empty($error_message) ): ?>
	<ul class="error_message">
		<?php foreach( $error_message as $value ): ?>
			<li>・<?php echo $value; ?></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<form method="post">
	<div>
		<label for="view_name">ハンドルネームでや～す♪</label>
		<input id="view_name" type="text" name="view_name" value="<?php if( !empty($_SESSION['view_name']) ){ echo $_SESSION['view_name']; } ?>">
  </div>
  
	<div>
		<label for="message">ここに投稿文書きや～す♪</label>
		<textarea id="message" name="message"><?php if( !empty($clean['message']) ){ echo $clean['message']; } ?></textarea>
  </div>
  
	<input type="submit" name="btn_submit" value="書き込む♪">
</form>

<hr>

<section>
<?php if( !empty($message_array) ): ?>
<?php foreach( $message_array as $value ): ?>
<article>
    <div class="info">
        <h2><?php echo $value['view_name']; ?></h2>
        <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
    </div>
    <p><?php echo nl2br($value['message']); ?></p>
</article>
<?php endforeach; ?>
<?php endif; ?>
</section>

</body>

</html>
