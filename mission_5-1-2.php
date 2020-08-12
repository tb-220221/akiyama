<?php
    // DB接続設定
    $dsn = 'データベース名';
	$user = 'ユーザー名';
	$password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));//PHP Data Objectsのインスタンス化

    // テーブル作成
    $sql = "CREATE TABLE IF NOT EXISTS mission_5( -- mission5のテーブルがないとき作成
        id INT AUTO_INCREMENT PRIMARY KEY, -- カラム名:id int型　自動的に１ずつ増加　PRIMARY KEY に指定
        name char(32), -- カラム名:name 32文字以内
        comment TEXT, -- カラム名:comment text型
        password char(32), -- カラム名:password 32文字以内
        date DATETIME -- カラム名:date DATETIME型
    )";
    $stmt = $pdo->query($sql);

    //変数初期化
    $name = null;
    $comment = null;
    $pass1 = null;
    $date = null;
    $errors = null;

    
    if(isset($_POST["submit"])){ // submitが押されたら実行
        if(isset($_POST["name"])){//$POST[]に値が入ってる時のみ（以下同じ）値が入ってない時のエラーメッセージ対策
            $name = $_POST["name"];//名前
        }
        if(mb_strlen($name) > 33){
            $errors = '名前は32文字以内で入力してください';
        }
        if(isset($_POST["comment"])){
            $comment = $_POST["comment"];//コメント
        }
        date_default_timezone_set('Asia/Tokyo');
        $date = date("Y/m/d H:i:s");//日付
        if(isset($_POST["password1"])){
            $pass1 = $_POST["password1"];//入力パスワード
        }
        if(isset($_POST["editNo"])){
            $editNo = $_POST["editNo"];//編集対象番号
        }
        
        if(empty($editNo) && !empty($name) && !empty($comment) && !empty($pass1)){//新規投稿
            $sql = $pdo -> prepare("INSERT INTO mission_5 (name, comment, password, date) VALUES (:name, :comment, :password, :date)");//プリペアドステートメントで SQLをあらかじめ用意しておく
            $sql -> bindParam(':name', $name, PDO::PARAM_STR);//bindParam()関数を使用することで、プレースホルダーに値をバインドさせることができる
            $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
            $sql -> bindParam(':password', $pass1, PDO::PARAM_STR);
            $sql -> bindParam(':date', $date, PDO::PARAM_STR);
            $sql -> execute(); 
        }else if(!empty($editNo) && !empty($name) && !empty($comment) && !empty($pass1)){//編集
            $sql = $pdo->prepare('UPDATE mission_5 SET name=:name, comment=:comment, password=:password WHERE id=:editNo');
            $sql->bindParam(':name', $name, PDO::PARAM_STR);
            $sql->bindParam(':comment', $comment, PDO::PARAM_STR);
            $sql->bindParam(':password', $pass1, PDO::PARAM_STR);
            $sql->bindParam(':editNo', $editNo, PDO::PARAM_INT);
            $sql->execute();
        }else{
            $errors = "名前またはコメントまたはパスワードを入力してください";
        }
    }
    
    if(isset($_POST["edit"])){ // editが押されたら実行
        if(isset($_POST["editNo"])){
            $editNo = $_POST["editNo"];//編集対象番号
        }
        if(isset($_POST["password2"])){
            $pass2 = $_POST["password2"];//編集パスワード
        }
        if(!empty($editNo)){// editNoが空でないなら実行
            if(!empty($pass2)){//パスワードが空でない場合実行
                $sql = $pdo->prepare('SELECT * FROM mission_5 WHERE id=:editNo');
                $sql->bindParam(':editNo', $editNo, PDO::PARAM_INT);
                $sql->execute();
                $selectedRows = $sql->fetchAll();
                if(!empty($selectedRows[0])){
                    $getcontents = $selectedRows[0];
                    if($pass2==$getcontents['password']){//編集パスワードと入力パスワードが一致
                        $name = $getcontents['name'];
                        $comment = $getcontents['comment'];
                        $pass1 = $getcontents['password'];
                    }else{//パスワードが一致しないとき
                        $errors = "正しいパスワードを入力してください";
                    }
                }else{
                    $errors = "編集対象番号がありません";
                }
            }else{
                $errors = "パスワードを入力して";
            }
        }else{
            $errors = "編集対象番号を入力して";
        }
    }

    if(isset($_POST["delete"])){ // deleteが押されたら実行
        if(isset($_POST["password3"])){
            $pass3 = $_POST["password3"];//削除パスワード
        }
        if(isset($_POST["deleteNo"])){
            $delNo = $_POST["deleteNo"];//削除対象番号
        }
        if(!empty($delNo)){// delNoが空でないなら実行
            if(!empty($pass3)){//パスワードが空でない場合実行
                $sql = $pdo->prepare('SELECT password FROM mission_5 WHERE id=:delNo');
                $sql->bindParam(':delNo', $delNo, PDO::PARAM_INT);
                $sql->execute();
                $selectedRows = $sql->fetchAll();
                if(!empty($selectedRows[0])){
                    $getcontents = $selectedRows[0];
                    if($pass3==$getcontents['password']){//編集パスワードと入力パスワードが一致
                        $sql = 'delete from mission_5 where id=:delNo';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':delNo', $delNo, PDO::PARAM_INT);
                        $stmt->execute();
                    }else{//パスワードが一致しないとき
                        $errors = "正しいパスワードを入力してください";
                    }
                }else{
                    $errors = "削除対象番号がありません";
                }
            }else{
                $errors = "パスワードを入力して";
            }
        }else{
            $errors = "削除対象番号を入力して";
        }
    }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission5</title>
</head>
<body>
    <form action="" method="post">
    <h1>投稿</h1>
    <p>入力フォーム</p>
        <input type="text" name="name" placeholder="名前は" value="<?php if(isset($name)) {echo $name;} ?>">
        <input type="text" name="comment" placeholder="ひとこと入れて" value="<?php if(isset($comment)) {echo $comment;} ?>">
        <input type="text" name="password1" placeholder="パスワード" value="<?php if(isset($pass1)) {echo $pass1;} ?>">
        <input type="submit" name="submit" value="送信">

    <p>編集フォーム</p>
        <input type="number" name="editNo" placeholder="編集対象番号を入力" value="<?php if(isset($editNo)) {echo $editNo;} ?>">
        <input type="text" name="password2" placeholder="パスワード">
        <input type="submit" name="edit" value="編集">

    <p>削除フォーム</p>
        <input type="number" name="deleteNo" placeholder="削除対象番号を入力">
        <input type="text" name="password3" placeholder="パスワード">
        <input type="submit" name="delete" value="削除">
    </form>
    <h1>掲示板</h1>

    <?php
        //入力したデータレコードを抽出し、ブラウザに表示する
        $sql = 'SELECT * FROM mission_5';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            //$rowの中にはテーブルのカラム名が入る
            echo $row['id'].'：';
            echo $row['name'].',';
            echo $row['comment'].',';
            echo $row['date'].'<br>';
        echo "<hr>";
        }
        if(!empty($errors)){//errorの表示固定
            echo $errors;
        }
    ?>
</body>
</html>