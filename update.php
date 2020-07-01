<html>
<head>
<title>データ編集</title>
<link rel="stylesheet" type="text/css" href="txtcolor.css">
<meta charset="UTF-8">
</head>
<body>
<?php

function getformvalue($name,$def="") {
	$hoge= "" ;
	if(isset($_GET[$name])){
		$hoge = $_GET[$name];
	} else {
		$hoge = $def;
	}
	return $hoge;
}

function postformvalue($name,$def=""){
	$hoge= "" ;
	if(isset($_POST[$name])){
		$hoge = $_POST[$name];
	} else {
		$hoge = $def;
	}
	return $hoge;
}

if($_SERVER["REQUEST_METHOD"] == "GET"){
	$vno = getformvalue('vno');
	$cn = getformvalue('cn');
	
	echo "{$vno}番地、CN{$cn}のデータを編集します。<br/>\n";
	echo "<a href='http://jinrou.dip.jp/~jinrou/kako/{$vno}.html' target='_blank'>当該ログはこちら</a><br>\n";
	echo "<br>\n";
	echo "<form method='post' target='update.php'>\n";
	echo "村の種別を変更<br>";
	echo "※特殊村は「特殊(なんとかかんとか)」で入力。特殊(探偵)とか。<br/>\n";
	echo "<select name='type'>\n";
	echo "<option value='nochange' selected>変更しない</option>\n";
	echo "<option value='normal'>普通</option>\n";
	echo "<option value='addjob'>特殊(役職追加)</option>\n";
	echo "<option value='addura'>占い増える</option>\n";
	echo "<option value='addfox'>妖狐増える</option>\n";
	echo "<option value='deathnote'>デスノ</option>\n";
	echo "<option value='devil'>デビトリ</option>\n";
	echo "<option value='custom'>その他(自由入力)</option>\n";
	echo "</select><br><br>\n";
	
	echo "<input type='text' name='custom'><br><br>\n";
	echo "役職を変更<br>\n";
	echo "<select name='job'>\n";
	echo "<option value='nochange' selected>変更しない</option>\n";
	echo "<option value='hum'>村人</option>\n";
	echo "<option value='ura'>占い</option>\n";
	echo "<option value='nec'>霊能</option>\n";
	echo "<option value='bgd'>狩人</option>\n";
	echo "<option value='fre'>共有</option>\n";
	echo "<option value='cat'>猫又</option>\n";
	echo "<option value='mad'>狂人</option>\n";
	echo "<option value='wlf'>人狼</option>\n";
	echo "<option value='fox'>妖狐</option>\n";
	echo "<option value='imo'>背徳</option>\n";
	echo "</select><br><br>\n";
	
	echo "勝敗を変更<br>\n";
	echo "<select name='result'>\n";
	echo "<option value='nochange' selected>変更しない</option>\n";
	echo "<option value='win'>勝利</option>\n";
	echo "<option value='lose'>敗北</option>\n";
	echo "</select><br><br>\n";
	
	echo "死亡日を変更<br>\n";
	echo "<select name='day'>\n";
	echo "<option value='nochange' selected>変更しない</option>\n";
	echo "<option value='change'>変更</option>\n";
	echo "</select><br>\n";
	echo "<input type='text' name='daytxt'><br><br>\n";
	
	echo "備考1を変更<br>\n";
	echo "<select name='bikou1'>\n";
	echo "<option value='nochange' selected>変更しない</option>\n";
	echo "<option value='change'>変更</option>\n";
	echo "</select><br>\n";
	echo "<input type='text' name='bikoutxt'><br><br>\n";
	
	echo "coを変更<br>\n";
	echo "<select name='co'>\n";
	echo "<option value='nochange' selected>変更しない</option>\n";
	echo "<option value='change'>変更</option>\n";
	echo "</select><br>\n";
	echo "<input type='text' name='cotxt'><br><br>\n";

	echo "<input type='submit'>\n";
	echo "<input type='hidden' value='{$vno}' name='vno'>\n";
	echo "<input type='hidden' value='{$cn}' name='cn'>\n";
	echo "</form>";
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$joblist = array(
		"hum"=>"村　人",
		"ura"=>"占い師",
		"nec"=>"霊能者",
		"fre" => "共有者", 
		"bgd" => "狩　人",
		 "cat" => "猫　又", 
		 "mad"=>"狂　人",
		 "wlf"=>"人　狼",
		 "fox"=>"妖　狐",
		 "imo"=>"背徳者"
	);
	
	$typelist = array(
		"normal"=>"普通",
		"addjob"=>"特殊(役職追加)",
		"addura"=>"占い増える",
		"addfox"=>"妖狐増える",
		"deathnote"=>"死神手帳",
		"devil"=>"デビトリ"
	);
	$resultlist = array(
		"win"=>"勝利",
		"lose"=>"敗北"
	);
	$vno = postformvalue('vno');
	$cn = postformvalue('cn');
	$type = postformvalue('type');
	$job = postformvalue('job');
	$custom = postformvalue('custom');
	$result = postformvalue('result');
	$bikou1 = postformvalue('bikou1');
	$bikoutxt = postformvalue('bikoutxt');
	$co = postformvalue('co');
	$cotxt = postformvalue('cotxt');
	$day = postformvalue('day');
	$daytxt = postformvalue('daytxt');

	$mysqli = new mysqli('127.0.0.1', 'mobajinro', '2SFMz5zE', 'mobajinro');
	if ($mysqli->connect_error) {
	    echo $mysqli->connect_error;
	    exit();
	} else {
	    $mysqli->set_charset("utf8");
	}
	
	if($job != "nochange") {
		$stmt = $mysqli -> prepare("UPDATE villager SET job = ? WHERE vno = ? and cn = ?");
		$stmt -> bind_param('sis',$joblist[$job],$vno,$cn);
		if( $stmt -> execute() ){
			echo "正常終了したと思う:役職を{$job}に変更<br>";
		} else {
			echo  $stmt->error;
		}
	}
	if($result != "nochange") {
		$stmt = $mysqli -> prepare("UPDATE villager SET result = ? WHERE vno = ? and cn = ?");
		$stmt -> bind_param('sis',$resultlist[$result],$vno,$cn);
		if( $stmt -> execute() ){
			echo "正常終了したと思う:結果を{$result}に変更<br>";
		} else {
			echo  $stmt->error;
		}
	}
	
	if($type != "nochange") {
	 	if(isset($typelist[$type])){
	 		$type2 = $typelist[$type];
	 	} else {
	 		$type2 = $custom;
	 	}
		$stmt = $mysqli -> prepare("UPDATE village SET type = ? WHERE vno = ?");
		$stmt -> bind_param('si',$type2,$vno);
		if( $stmt -> execute() ){
			echo "正常終了したと思う:村種類を{$type}に変更<br>";
		} else {
			echo  $stmt->error;
		}
	}
	if($day != "nochange") {
		$stmt = $mysqli -> prepare("UPDATE villager SET day = ? WHERE vno = ? and cn = ?");
		$stmt -> bind_param('sis',$daytxt,$vno,$cn);
		if( $stmt -> execute() ){
			echo "正常終了したと思う:死亡日を{$daytxt}に変更<br>";
		} else {
			echo  $stmt->error;
		}
	}
	if($bikou1 != "nochange") {
		$stmt = $mysqli -> prepare("UPDATE villager SET bikou1 = ? WHERE vno = ? and cn = ?");
		$stmt -> bind_param('sis',$bikoutxt,$vno,$cn);
		if( $stmt -> execute() ){
			echo "正常終了したと思う:備考1を{$bikoutxt}に変更<br>";
		} else {
			echo  $stmt->error;
		}
	}
	if($co != "nochange") {
		$stmt = $mysqli -> prepare("UPDATE village SET co = ? WHERE vno = ?");
		$stmt -> bind_param('si',$cotxt,$vno);
		if( $stmt -> execute() ){
			echo "正常終了したと思う:COを{$cotxt}に変更<br>";
		} else {
			echo  $stmt->error;
		}
	}
	echo "完了";
	$mysqli-> close();
}
?>
</body>
</html>