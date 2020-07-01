<html>
<head>
<title>タグ編集</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="main.css">
<script type="text/javascript" src="jquery-3.2.0.min.js"></script>
<style>
body{
	margin:5px;
}
.tag{
	color:blue;
}
</style>
</head>
<body>
<!--nobanner-->

<?php

#--------db初期設定------------

$mysqli = new mysqli('127.0.0.1', 'mobajinro', '2SFMz5zE', 'mobajinro');
if ($mysqli->connect_error) {
    echo $mysqli->connect_error;
    exit();
} else {
    $mysqli->set_charset("utf8");
}

#--------GETからもろもろ取得------------

if(isset($_GET['vno'])){
	$vno = $_GET['vno'];
	$getflg = true;
} else {
	$getflg = false;
}

$post = array();
if(isset($_POST)){
	$postflg = true;
	foreach($_POST as $k => $v){
		$post[$k] = $v;
	}
}

if($getflg){

	$sql = "SELECT name,tag FROM village where vno = {$vno}";
	if ($result = $mysqli -> query($sql)) {
		while($row = $result->fetch_assoc()) {
			$hoge[] = $row;
		}
	}
	$data = $hoge[0];
	$vname = $data['name'];
	$tags = explode(",",$data['tag']);
	
	echo "{$vno}番地 {$vname} のタグを編集します。<br>\n<br>\n";
	echo "削除したいタグには削除のチェックを、<br>追加したいタグは行を分けてテキストボックスに入力してください。<br>\n<br>\n";
	echo "<form action='tagedit.php' method='post'>";
	$i=0;
	if($data['tag'] != ""){
		foreach ($tags as $t) {
			echo "【<span class='tag'>{$t}</span>】 <label>削除:<input type='checkbox' name='del{$i}'><br></label>\n";
			echo "<input type='hidden' name='tag{$i}' value='{$t}'>\n";
			$i++;
		}
	} else {
		echo "現在、登録されているタグはありません。<br>\n";
	}
	echo "<br>\n";
	echo "<textarea name='tagadd' rows='5' cols='40' placeholder='追加したいタグを改行区切りで入力'></textarea>";
	echo "<input name='vno' type='hidden' value='{$vno}'>";
	echo "<input type='submit'>";
	echo "</form>";
}
elseif($postflg){
	$tags = array();
	$i = 0;
	$vno = $post['vno'];
	
	while(true){
		$d = "del{$i}";
		$t = "tag{$i}";
		if(isset($post[$t])){
			if(!$post[$d]){
				$tags[] = $post[$t];
			}
		} else {
			break;
		}
		$i++;
	}
	$tagsadd = explode("\r\n",trim($post['tagadd']));
	if($tagsadd[0]!="") $tags = array_merge($tags,$tagsadd);
	$tag = implode(",",$tags);
	
	$stmt = $mysqli -> prepare("UPDATE village SET tag = ? where vno = ?");
	$stmt -> bind_param('si', $tag, $vno);
	if( $stmt -> execute() ){
	} else {
		echo  $stmt->error;
	}
	echo "正常に変更が行われました。<br>このウィンドウは自動で閉じます。\n";
	echo <<<EOM
<script>
window.onload = function(){
	setTimeout(function(){
		window.close();
	},3000);
}
</script>
EOM;
}
$mysqli->close();
?>
</body>
</html>