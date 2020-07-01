<html>
<head>
<meta charset="UTF-8">
</head><body>

<?php

function getDeath($cn){
	global $html;
	$day = "";
	$reason = "";
	$cn = str_replace("\\","\\\\\\",$cn);
	$cn = str_replace("/","\/",$cn);
	$cn = str_replace("'","\'",$cn);
	$cn = str_replace("*","\*",$cn);
	
	$ismatch = preg_match("/<b>".$cn."<\/b>さんは翌日無残な姿で発見された・・・。(.+?)([１0-9]{1,2})日目の夜となりました。/us",$html,$bitten);
	if($ismatch == 1){
		$reason .=  "無残";
		if($bitten[2] == "１"){
			$day = "2日目朝";
		}
		else {
			$foo = (int)$bitten[2]+1;
			$day = (string)$foo."日目朝";
		}
	}
	$ismatch = preg_match("/<b>".$cn."<\/b>さんは翌日に死体で発見された・・・。(.+?)([0-9]{1,2})日目の夜となりました。/us",$html,$dnote);
	if($ismatch == 1){
		$reason .=  "デスノ";
		$foo = (int)$dnote[2]+1;
		$day = (string)$foo."日目朝";
	}
	$ismatch = preg_match("/<b>".$cn."<\/b>さんは昨夜何者かによって射殺された・・・。(.+?)([0-9]{1,2})日目の夜となりました。/us",$html,$devil);
	if($ismatch == 1){
		$reason .=  "デビトリ";
		$foo = (int)$devil[2]+1;
		$day = (string)$foo."日目朝";
	}
	$ismatch = preg_match("/<b>".$cn."<\/b>さんは村民協議の結果処刑されました・・・。(.+?)([0-9]{1,2})日目の朝となりました。/us",$html,$exc);
	if($ismatch == 1){
		$reason .=  "処刑";
		$foo = (int)$exc[2];
		$day = (string)$foo."日目夕";
	}
	$ismatch = preg_match("/<b>".$cn."<\/b>さんは猫又の呪いで死亡しました・・・。(.+?)([0-9]{1,2})日目の朝となりました。/us",$html,$fellow);
	if($ismatch == 1){
		$reason .=  "道連れ";
		$foo = (int)$fellow[2];
		$day = (string)$foo."日目夕";
	}
	$ismatch = preg_match("/<b>".$cn."<\/b>さんは都合により突然死しました・・・。(.+?)([0-9]{1,2})日目の朝となりました。/us",$html,$goafter);
	if($ismatch == 1){
		$reason =  "後追い";
		$foo = (int)$goafter[2];
		$day = (string)$foo."日目";
	}
	$ismatch = preg_match("/<b>".$cn."<\/b>さんは都合により突然死しました・・・。【ペナルティ】(.+?)([0-9]{1,2})日目の朝となりました。/us",$html,$totsu);
	if($ismatch == 1){
		$reason =  "突然死";
		$foo = (int)$totsu[2];
		$day = (string)$foo."日目";
	}
	
	if ($day == "") $day = "生存";
	if ($reason == "") $reason = "生存";
	
	return array ($day, $reason);
}
	
function getfortune($cn){
	global $html;
	$cn = str_replace("\\","\\\\\\",$cn);
	$cn = str_replace("/","\/",$cn);
	$cn = str_replace("'","\'",$cn);
	$cn = str_replace("*","\*",$cn);

	$bikou  = "占い";
	$ismatch = preg_match_all("/<b>".$cn."<\/b>さんを占います。 >>> (.+?) <<</us",$html,$fortune);
	if ($ismatch >= 1){
		for ($i=0;$i<count($fortune[1]);$i++) $bikou .= " ".$fortune[1][$i];
	} else {
		$bikou .= " 初日犠牲者";
	}
	return $bikou;
}

function pickdeathnote($cn){
	global $html;
	$cn = str_replace("\\","\\\\\\",$cn);
	$cn = str_replace("/","\/",$cn);
	$cn = str_replace("'","\'",$cn);
	$cn = str_replace("*","\*",$cn);

	$ismatch = preg_match_all("/<b>".$cn."<\/b>さんに「死神の手帳」を/u",$html,$picknote);
	if ($ismatch > 0) {
		$n = "デスノ".(string)$ismatch."回";
	} else {
		$n = "";
	}
	return $n;
}

function pickdevil($cn){
	global $html;
	$cn = str_replace("\\","\\\\\\",$cn);
	$cn = str_replace("/","\/",$cn);
	$cn = str_replace("'","\'",$cn);
	$cn = str_replace("*","\*",$cn);

	$ismatch = preg_match_all("/<b>".$cn."<\/b>さんにデビルトリガーを進呈/u",$html,$pickdevi);
	if($ismatch == 1){
		$n = "デビトリ進呈";
	} else {
		$n = "";
	}
	return $n;
}

function judgeresult($my,$win){
	$result = "";
	if($my=="村　人" or $my == "占い師" or $my == "霊能者" or $my == "狩　人" or $my == "共有者" or $my == "猫　又"){
		$myside = "村　人";
	} elseif ($my == "人　狼" or $my == "狂　人" or $my == "狂信者") {
		$myside = "人　狼";
	} elseif ($my == "妖　狐" or $my == "背徳者") {
		$myside = "妖　狐";
	}
	if( $myside == $win or ($myside == "村　人" and $win == "猫　又")){
		$result = "勝利";
	} else {
		$result = "敗北";
	}
	if ($win == "引き分け"){
		$result = "引き分け";
	}
	if ($win == "廃村"){
		$result = "廃村";
	}
	return $result;
}



################ ここからメイン
################ ここからメイン
################ ここからメイン

$blank = "";

// mysqliクラスのオブジェクトを作成
$mysqli = new mysqli('127.0.0.1', 'mobajinro', '2SFMz5zE', 'mobajinro');
if ($mysqli->connect_error) {
    echo $mysqli->connect_error;
    exit();
} else {
    $mysqli->set_charset("utf8");
}
#$vlist = file("list.txt",FILE_IGNORE_NEW_LINES);
if(isset($_GET['vlist'])){
	$hoge = trim($_GET['vlist']);
	$hoge = str_replace(" ","\r\n",$hoge);
	$vlist = explode("\r\n",$hoge);
	for($i=0; $i<count($vlist); $i++){
		$vlist[$i] = trim($vlist[$i]);
	}
} else {
	$vlist = array();
	echo "村リストがないって判定されてるみたいだよー(o・▽・o)1";
}
if(count($vlist)==0){
	echo "村リストがないって判定されてるみたいだよー(o・▽・o)2";
	echo $hoge;
}

if(isset($_GET['mode'])){
	$mode = $_GET['mode'];
} else {
	$mode = "normal";
}

for ($c=0;$c<count($vlist);$c++){
		
	if($mode == "normal"){
		$html = file_get_contents("http://jinrou.dip.jp/~jinrou/kako/".$vlist[$c].".html");
	} else {
		$html = file_get_contents("http://jinrou.dip.jp/~jinrou/cgi_jinro.cgi?COMMAND=LOGVIEW&TXTLOGIN=1&STORYTYPE=3&VILLAGENO=".$vlist[$c]);
	}
	$html = mb_convert_encoding($html,"UTF-8", "sjis-win" );
	
	$html = preg_replace('/color\=\"[^>]*\">/u','>',$html);
	$html = preg_replace("/<\/?font ?>/u",'',$html);
	$html = preg_replace("/1日目に食われる運命です/u",'',$html);
	
	$rule = "";
	$fre = array();
	$npcjob = "";
	$totsu = 0;
	
	
	preg_match( "/<title>(.*?)<\/title>/u", $html, $title);
	preg_match("/(\d{6})番 (.+)$/u", $title[1] , $vname);
	#echo "村番号:".$vname[1]."<br/>\n";
	#echo "村名:".$vname[2]."<br/>\n";
	
	$ismat = preg_match("/「(.　.)」の勝利です！\((..\/..\/.. ..:..:..)\)/u",$html,$enddata);
	if ($ismat == 1){
		#echo "勝利陣営:".$enddata[1]."<br/>\n";
		#echo "終了日時:".$enddata[2]."<br/>\n";
	} else {
		$ismat = preg_match("/「(引き分け)」です！\((..\/..\/.. ..:..:..)\)/u",$html,$enddata);
		if($ismat == 1){
			$enddata[1] = "引き分け";
		} else {
			$enddata[1] = "廃村";
		}
		#echo "勝利陣営:引き分け<br/>\n";
		#echo "終了日時:".$enddata[2]."<br/>\n";
	}
	
	preg_match("/(\d\d?)日目/u",$html,$endday);
	//echo "終了日:".$endday[0]."<br/>\n";
	$enddayint = (int)$endday[1];
	$endday[0] = str_replace("目","",$endday[0]);
	
	
	
	preg_match("/終了<b>\[(.*)\]<\/b>/u",$html,$time);
	//echo "所要時間:".$time[1]."<br/>\n";
	$time[1] = str_replace("時間","h",$time[1]);
	$time[1] = str_replace("分","m",$time[1]);
	
	$time2 = $endday[0]."/".$time[1];
	#echo "経過時間:".$time2."<br/>\n"; 
	
	preg_match("/<td>「 配役の人数は、(.+?)です。」<\/td>/u",$html,$casttype);
	#echo "配役:".$casttype[1]."<br/>\n";
	
	preg_match("/<td>「--- (.*?) ---」<\/td>/u",$html,$giventime);
	#echo "制限時間:".$giventime[1]."<br/>\n";
	
	preg_match_all("/<td>([^<>]+?)<br><b>([^<>]+?)<\/b><br>\[(...)\]<br>（(...)）<\/td>/u",$html,$player);
	$num = count($player[0]);
	
	if (strpos($giventime[1],'【死神手帳】') !== false){
		$rule= "死神手帳";
	} elseif (strpos($giventime[1],'【デビトリ】') !== false) {
		$rule = "デビトリ";
	} elseif (strpos($giventime[1],'【デビ手帳】') !== false) {
		$rule = "デビ手帳";
	} elseif (strpos($giventime[1],'夜:3分0秒') === false) {
		$rule = "役職追加";
	} 
	
	if($rule == ""){
		if ($num <= 14) {
			if (strpos($casttype[1],'妖狐1') === false){
				$rule = "普通(妖狐なし)";
			} else {
				$rule = "普通";
			}
		} elseif ($num <= 18) {
			$rule = "普通";
		} elseif ($num == 19) {
			if (strpos($casttype[1],'妖狐1') === false){
				$rule = "妖狐増える";
			} else {
				$rule = "普通";
			}
		} elseif ($num >=20 and $num <=24) {
			if (strpos($casttype[1],'占い師2') !== false){
				$rule = "占い増える";
			} elseif (strpos($casttype[1],'妖狐2') !== false) {
				$rule = "妖狐増える";
			} else {
				$rule = "普通";
			}
		} else {
			if (strpos($casttype[1],'占い師2') !== false){
				$rule = "占い増える";
			} elseif (strpos($casttype[1],'妖狐3') !== false) {
				$rule = "妖狐増える";
			} else {
				$rule = "普通";
			}
		}
	}
	
	if(($num >=13 and $num <=15)or($num == 19)){
		$ismatch = preg_match("/◆<b>ゲームマスター<\/b><\/td><td>「狂人の人は/u",$html,$setspecial);
		if($ismatch){
			$rule = "役職追加";
		}
	}
	
	
	#echo "ルール:".$rule."<br/>\n";
	
	#村人の情報取得
	for ($i=0; $i<$num; $i++){
	
		$cn = $player[1][$i];
		
		
		#HNとトリップ分離の処理
		if(strpos($player[2][$i],'◆') !== false){
			preg_match("/◆[A-Za-z0-9\/\.]{10}/u",$player[2][$i],$hoge);
			$trip = $hoge[0];
		} else {
			$trip="-";
		}
		$hn = str_replace(' '.$trip,'',$player[2][$i]);
		$hn = trim($hn);
		if($hn == '') $hn="-";
		
		#死亡関係取得
		list ($deathday, $deathreason) = getDeath($cn);
		
		#例外処理
		if($deathday == "生存" and $player[4][$i] == "死　亡"){
			$deathday = (string)($enddayint-1)."日目夕";
			$deathreason = "処刑";
		}
		if($player[3][$i] == "妖　狐" and $deathreason == "無残"){
			$deathreason = "呪殺";
		}

		if($player[3][$i] == "狂　人" and $rule == "役職追加"){
			$player[3][$i] = "狂信者";
		}

		if($deathreason == "後追い"){
			$player[3][$i] = "背徳者";
		}
		
		#勝敗取得
		$myresult = judgeresult($player[3][$i],  $enddata[1]);
		
		$bikou1 = "";
		$bikou2 = "";
		
		#呪殺した占い師の取得
		if($deathreason == "呪殺"){
			$bikou1 = getfortune($cn);
		}
		
		#共有の取得(あとで使う)
		if($player[3][$i] == "共有者"){
			array_push($fre,$i);
		}
		
		#初日処理
		if ($hn == "初日犠牲者"){
			$trip = "◆_shonichi_";
			$npcjob = $player[3][$i];
		}
		
		#突然死処理
		if ($deathreason == "突然死"){
			$totsu += 1;
		}
	
		#デスノート回数取得
		if($rule == "死神手帳" or $rule == "デビ手帳"){
			$bikou2 = pickdeathnote($cn);
		}
		
		#デビトリ取得
		if($rule == "デビトリ" or $rule == "デビ手帳"){
			$bikou2 .= pickdevil($cn);
		}
		
		$plr[$i]['cn'] = $cn;
		$plr[$i]['hn'] = $hn;
		$plr[$i]['trip'] = $trip;
		$plr[$i]['job'] = $player[3][$i];
		$plr[$i]['result'] = $myresult;
		$plr[$i]['day'] = $deathday;
		$plr[$i]['reason'] = $deathreason;
		$plr[$i]['bikou1'] = $bikou1;
		$plr[$i]['bikou2'] = $bikou2;
	}
	
	#共有処理
	foreach($fre as $i){
		$plr[$i]['bikou1'] .= "相方";
		foreach ($fre as $j){
			if($i != $j) 	$plr[$i]['bikou1'] .= " ".$plr[$j]['cn'];
		}
	}
	
	#echo "突然死:".$totsu."人<br/>\n";
	#echo "初日役職:".$npcjob."<br/>\n";
	
	#表示
	#echo "村人一覧(".$num."人)<br/>\n";
	#echo "<table>";
	#echo "<tr><td>CN</td><td>HN</td><td>トリップ</td><td>役職</td><td>勝敗</td><td>死亡日</td><td>死因</td><td>備考1</td><td>備考2</td></tr>\n";
	#for ($i=0; $i<$num; $i++){
		#echo "<tr><td>".$plr[$i]['cn']."</td><td>".$plr[$i]['hn']."</td><td>".$plr[$i]['trip']."</td><td>".$plr[$i]['job']."</td><td>".$plr[$i]['result']."</td><td>".$plr[$i]['day']."</td><td>".$plr[$i]['reason']."</td><td>".$plr[$i]['bikou1']."</td><td>".$plr[$i]['bikou2']."</td></tr>\n";
	#}
	#echo "</table>";

	
	$stmt = $mysqli -> prepare("SELECT * FROM village WHERE vno = ?");
	$stmt -> bind_param('i', $vname[1]);
	$stmt -> execute();
	$stmt -> store_result();
	$flg = $stmt->num_rows;
	
	if ($flg == 0){
		$stmt = $mysqli -> prepare("INSERT INTO village VALUES(?,?,?,?,?,?,?,?,?,?,?)");
		echo  $stmt->error;
		$stmt -> bind_param('isisssssiss', $vname[1], $vname[2],$num,$rule,$enddata[2],$enddata[1],$time2,$npcjob,$totsu,$blank,$blank);
		echo  $stmt->error;
		if( $stmt -> execute() ){
		} else {
			echo  $stmt->error;
		}
		
		for ($i=0;$i<$num;$i++){
			$stmt = $mysqli -> prepare("INSERT INTO villager VALUES(?,?,?,?,?,?,?,?,?,?,?)");
			echo  $stmt->error;
			$stmt -> bind_param('issssssssss', $vname[1], $plr[$i]['cn'], $plr[$i]['hn'], $plr[$i]['trip'], $plr[$i]['job'], $plr[$i]['result'], $plr[$i]['day'], $plr[$i]['reason'], $plr[$i]['bikou1'], $plr[$i]['bikou2'], $blank);
			echo  $stmt->error;
			$stmt -> execute();
			echo  $stmt->error;
		}
		echo "$vname[1] 番地 db登録完了。<br>\n";
	} else {
		echo "この村($vname[1] 番地)はすでに登録されている模様\n";
	}

}
// DB接続を閉じる
$mysqli->close();

?>
</body></html>