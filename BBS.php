<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>BBS</title>
</head>
<body>
    
    <?php
        // id改良版
        echo "簡易掲示板<br>";
        echo "テーマ：最近よく聞く曲orおすすめの曲は何ですか？<br>";
        echo "お名前と曲名とパスワードを入力し、できれば削除と編集もお願いします！<br>";
        
        
        /// DB接続設定
        $dsn = 'データベース名';
        $user = 'ユーザー名';
        $password = 'パスワード';
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        
        // テーブルの作成
        $sql = "CREATE TABLE IF NOT EXISTS BBS_N"
        ." ("
        . "id INT,"
        . "name char(32),"
        . "comment TEXT,"
        . "date TEXT,"
        . "password TEXT"
        .");";
        $stmt = $pdo->query($sql);
        
        
        //投稿
        //投稿フォームで受け取りが空でなかったら以下を実行
        if(!empty($_POST["name"]) && !empty($_POST["comment"]) && empty($_POST["num_edit"])){ //パスワードの入力がなくても一応投稿できるようにする
            //データの受け取り
            $comment = $_POST["comment"]; 
            $name = $_POST["name"];
            $password = $_POST["password_new"];
            // 受け取り完了を表示
            echo $name."さん、".$comment." を受け付けました<br>";
            //投稿日時の設定
            $date = date("Y/m/d H:i:s");
            //idの設定
            //もしレコードがない時はid=1 それ以外は既存のレコード数+1
            $sql = "SELECT * FROM BBS_N";
            $result = $pdo->query($sql);
            $cnt = $result->rowCount();
            if($cnt != 0){
                $id = $cnt + 1;
            }else{
                $id = 1;
            }
            
            // レコードの挿入
            $sql = $pdo -> prepare("INSERT INTO BBS_N (id, name, comment, date, password) VALUES (:id, :name, :comment, :date, :password)");
            $sql -> bindParam(':id', $id, PDO::PARAM_INT);
            $sql -> bindParam(':name', $name, PDO::PARAM_STR);
            $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
            $sql -> bindParam(':date', $date, PDO::PARAM_STR);
            $sql -> bindParam(':password', $password, PDO::PARAM_STR);
            $sql -> execute();
        
        
        // 削除
        // 削除フォームで受け取りが空でなかったら以下を実行       
        }elseif(!empty($_POST["number_delete"]) && !empty($_POST["password_delete"])){
            //データ受け取り
            $number_delete = $_POST["number_delete"];
            $password_delete = $_POST["password_delete"];
            //削除番号のidのレコードをDBから取り出し
            $id = $number_delete ; // idがこの値のデータだけを抽出したい、とする
            $sql = 'SELECT * FROM BBS_N WHERE id=:id ';
            $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
            $stmt->execute();                             // ←SQLを実行する。
            $results = $stmt->fetchAll(); 
            foreach ($results as $row){
                //$rowの中にはテーブルのカラム名が入る
                //パスワードが一致したら、削除する
                if($row['password'] == $password_delete){
                    $sql = 'delete from BBS_N where id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
            //idが削除番号（$number_delete）より大きいものは-1する
            $sql = "UPDATE BBS_N SET id=id-1 WHERE id > $number_delete";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        
            echo "削除しました";
        
        
        //編集
        //編集フォームで受け取りが空でなかったら以下を実行    
        }elseif(!empty($_POST["number_edit"]) && !empty($_POST["password_edit"])){
            //データ受け取り
            $number_edit = $_POST["number_edit"];
            $password_edit = $_POST["password_edit"];
            //編集番号のidのレコードをDBから取り出し
            $id = $number_edit ; // idがこの値のデータだけを抽出したい、とする
            $sql = 'SELECT * FROM BBS_N WHERE id=:id ';
            $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
            $stmt->execute();                             // ←SQLを実行する。
            $results = $stmt->fetchAll(); 
            foreach ($results as $row){
                //$rowの中にはテーブルのカラム名が入る
                //パスワードが一致したら、中身を取り出してフォームに表示する
                if($row['password'] == $password_edit){
                    //番号と名前とコメントとパスワードの取得
                    $e_num = $row['id'];
                    $name_exist = $row['name'];
                    $comment_exist = $row['comment'];
                    $password_exist = $row['password'];
                    //既存の投稿フォームに、取得した値を代入する
                    //formのvalue属性で対応
                }
            }
            
        //編集か新規登録か判断し、編集の場合以下を実行    
        }elseif((!empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["num_edit"]))){
            //データを受け取る
            $name_edit = $_POST["name"];
            $comment_edit = $_POST["comment"];
            $num_edit = $_POST["num_edit"];
            //投稿日時の再設定
            $date = date("Y/m/d H:i:s"); 
            //編集番号のidのレコードをDBから取り出し
            // レコードの編集
            //bindParamの引数（:nameなど）は4どんな名前のカラムを設定したかで変える必要がある。
            $id = $num_edit; //変更する投稿番号
            $name = $name_edit;
            $comment = $comment_edit; 
            $sql = 'UPDATE BBS_N SET name=:name,comment=:comment,date=:date WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->execute();
            
            echo "編集しました";
        }
    ?>
    
    
    <!--入力フォームの設定-->
    <form action="" method="post">
            <!--名前フォーム-->
            <input type="text" name="name" placeholder="お名前" value="<?php if(isset($name_exist)) {echo $name_exist;} ?>">
            <!--コメントフォーム-->
            <input type="text" name="comment" placeholder="曲名" value="<?php if(isset($comment_exist)) {echo $comment_exist;} ?>"><br>
            <!--編集したい投稿番号のテキストボックス-->
            <input type="hidden" name="num_edit" value="<?php if(isset($e_num)) {echo $e_num;} ?>">
            <!--パスワードフォーム-->
            <input type="text" name="password_new" placeholder="パスワード" ><br>
            <!--送信ボタン-->
            <input type="submit" name="submit"><br>
            <!--削除番号フォーム-->
            <input type="number" name="number_delete" placeholder="削除番号を入力"><br>
            <!--パスワードフォーム-->
            <input type="text" name="password_delete" placeholder="パスワード"><br>
            <!--削除ボタン-->
            <input type="submit" name="delete" value="削除"><br> 
            <!--編集番号フォーム-->
            <input type="number" name="number_edit" placeholder="編集番号を入力"><br>
            <!--パスワードフォーム-->
            <input type="text" name="password_edit" placeholder="パスワード"><br>
            <!--編集ボタン-->
            <input type="submit" name="edit" value="編集"><br><br>
    </form>
    
    
    <?php
        // レコードの抽出・表示
        //$rowの添字（[ ]内）は、作成したカラムの名称に併せる必要あり。
        echo "受付メッセージ一覧";
        echo "<hr>";
        $sql = 'SELECT * FROM BBS_N';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            //$rowの中にはテーブルのカラム名が入る
            echo $row['id'].', ';
            echo $row['name'].', ';
            echo $row['comment'].', ';
            echo $row['date'].'<br>';
            echo "<hr>";
        }
    ?>
    
</body>
</html>