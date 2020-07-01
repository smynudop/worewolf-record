<html>
<head>
<meta charset=UTF-8>
</head>
<body>
<?php

$cnt = 0;
$addlog = "";
$mode = "normal";

// mysqliクラスのオブジェクトを作成
$mysqli = new mysqli('127.0.0.1', 'mobajinro', '2SFMz5zE', 'mobajinro');
if ($mysqli->connect_error) {
    echo $mysqli->connect_error;
    exit();
} else {
    $mysqli->set_charset("utf8");
}

$sql = "SELECT MAX(vno) AS vno_newest FROM village";

if ($result = $mysqli -> query($sql)) {
	while($row = $result->fetch_assoc()) {
		$vno_newest = $row["vno_newest"];
	}
}

echo "最新:{$vno_newest}番地です(o・▽・o)<br>\n";
echo "未登録のログを検索……<br><br>\n\n";

// かころぐしゅとく
$html = file_get_contents("http://jinrou.dip.jp/~jinrou/cgi_jinro.cgi?log");
$html = mb_convert_encoding($html,"UTF-8", "sjis-win" );

preg_match_all("/(......)番 (【モバマス】[^<]+村)<\/a>/",$html,$vils);

foreach($vils[1] as $k => $v) {
	if($v > $vno_newest){
		$addlog .= $v . " ";
		echo $vils[0][$k]."<br/>\n";
	}
}

if($addlog == ""){
	$mode = "dat";
	echo "dat落ちしていないログを検索……<br><br>";
	preg_match_all("/(......)番 (【モバマス】[^<]+村)<\/option>/",$html,$vils);
	foreach($vils[1] as $k => $v) {
		if($v > $vno_newest){
			$addlog .= $v . " ";
			echo $vils[0][$k]."<br/>\n";
		}
	}
}

echo "<br>";

$addlog = trim($addlog);

if ($addlog) {
	echo "登録する場合は送信ボタンを押してねー(o・▽・o)";
	echo "<form action='addnewlog.php'target='_blank' method='get'><input type='hidden' value='{$addlog}' name='vlist'><input type='hidden' name='mode' value='{$mode}'><input type='submit'></form>";
} else {
	echo "新しいログはないっぽいです(o・▽・o)<br>\n";
}
?>
</body>
</html>