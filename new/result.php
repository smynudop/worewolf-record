<html>
<head>
<title>検索結果</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width">
<link rel="stylesheet" type="text/css" href="./new/main.css" id="pagetheme">
<link rel="icon" href="/favicon.ico">
<script type="text/javascript" src="jquery-3.2.0.min.js"></script>
<script type="text/javascript" src="./new/narrow.js"></script>
</head>
<body>
<!--nobanner-->

<?php

#--------変数設定------------

$jobclass = array(
	"村　人"=>"vil",
	"占い師"=>"ura",
	"霊能者"=>"nec",
	"狩　人"=>"bgd",
	"共有者"=>"fre",
	"猫　又"=>"cat",
	"人　狼"=>"wlf",
	"狂　人"=>"mad",
	"狂信者"=>"mad",
	"妖　狐"=>"fox",
	"背徳者"=>"imo"
);

$resultclass = array(
	"勝利"=>"win",
	"敗北"=>"lose",
	"引き分け"=>"draw",
	"廃村"=>"haison"
);

$typeclass = array(
	"普通"=>"normal",
	"普通(妖狐なし)"=>"normal",
	"役職追加"=>"addjob",
	"妖狐増える"=>"addfox",
	"占い増える"=>"addura",
	"死神手帳"=>"deathnote",
	"デビトリ"=>"devil",
	"デビ手帳"=>"devildeath"
);

$menulist = array(
	'村番号',
	'村名', 
	'CN', 
	'HN', 
	'トリップ',
	'役職',
	'勝敗',
	'死亡日',
	'死亡理由',
	'人数',
	'配役',
	'終了日時',
	'勝利陣営',
	'時間', 
	'初日役職',
	'備考1',
	'備考2',
	'タグ',
	''
);

$menu[0] = array(
	'vno' => '村番号',
	'name'=>'村名',
	'cn' => 'CN',
	'hn' => 'HN',
	'trip'=>'トリップ',
	'job'=>'役職',
	'result'=>'勝敗',
	'day'=>'死亡日',
	'reason'=>'死亡理由',
	'num'=>'人数',
	'type'=>'配役',
	'enddate'=>'終了日時',
	'win'=>'勝利陣営',
	'time'=>'時間',
	'npcjob' => '初日役職',
	'bikou1'=>'備考1',
	'bikou2' =>'備考2',
	'tag' => 'タグ',
	'tags' => array('タグ'),
	'visible' => true,
	'typeclass' => ""
);

$searchkeys = array(
	"cn",
	"CN",
	"hn",
	"HN",
	"trip",
	"TRIP",
	"job",
	"result",
	"day",
	"reason",
	"bikou1",
	"bikou2",
	"vno"
);

$debug = 0;
$datanum = 0;
$typetmp="";
unset($vdata);
unset($data);

function addquote($v){
	return '"'.$v.'"';
};

#--------db初期設定------------

$mysqli = new mysqli('127.0.0.1', 'mobajinro', '2SFMz5zE', 'mobajinro');
if ($mysqli->connect_error) {
    echo $mysqli->connect_error;
    exit();
} else {
    $mysqli->set_charset("utf8");
}

#--------GETからもろもろ取得------------

if(isset($_GET['query'])){
	$word = $_GET['query'];
	if($word == "") $word = "◆_shonichi_";
} else {
	$word = "◆_shonichi_";
}

if($word == "◆_shonichi_"){
	$word_disp = "";
} else {
	$word_disp = $word;
}

if(isset($_GET['operator'])){
	$operator = $_GET['operator'];
} else {
	if(isset($_GET['bool'])){
		$operator = $_GET['bool'];
		if ($operator == "") $operator = "OR";
	} else {
		$operator = "OR";
	}
}

if(isset($_GET['reverse'])){
	if($_GET['reverse'] == "on") {
		$reverse = true;
	} else {
		$reverse = false;
	}
} else {
	$reverse = false;
}

$reverse_str = $reverse ? "on" : "off";

$words = trim($words);
$words = explode("\r\n",$word);
for($i=0;$i<count($words);$i++){
	$words[$i] = explode(":",$words[$i]);
	if(!isset($words[$i][1])){
		$words[$i][1] = $words[$i][0];
		$words[$i][0] = "trip";
	}
}

#--------ログ書き込み---------------

$nowdata = date("ymd-His");
$hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
$wordoneline = str_replace("\r\n", "", $word);
$accessdata = implode("\t",array($nowdata,$hostname,$wordoneline,"\n"));
file_put_contents("accesslog.csv", $accessdata, FILE_APPEND);

#--------SQL文作り、取得------------

$cond = "";
$cond2 = "";
$unitflg = 0;
for($i=0;$i<count($words);$i++){
	if($words[$i][0] == "debug"){
		$debug = 1;
	} elseif(substr($words[$i][0],0,1)=="#") {
		$cond2 .= "AND ". substr($words[$i][0],1) ."='". $words[$i][1] ."'";
	} elseif($words[$i][0] == "tag" or $words[$i][0] == "TAG"){
		$cond2 .= "AND ".$words[$i][0] ." LIKE '%". $words[$i][1] ."%'";
	} elseif($debug or in_array($words[$i][0],$searchkeys)) {
		if($unitflg) {
			$cond .= " ".$operator;
		} else {
			$unitflg = 1;
		}
		$cond .= " ".$words[$i][0] ."='". $words[$i][1] ."'";
	}
}

$sql = "SELECT * FROM villager where {$cond} ORDER BY vno";

if ($result = $mysqli -> query($sql)) {
	while($row = $result->fetch_assoc()) {
		$data[] = $row;
	}
}

$sql2 = "SELECT * FROM village where vno IN (SELECT vno FROM villager where {$cond}) {$cond2} ORDER BY vno";

if ($result = $mysqli -> query($sql2)) {
	$i=0;
	while($row = $result->fetch_assoc()) {
		if (isset($typeclass[$row['type']])) {
			$row['typeclass'] = $typeclass[$row['type']];
		} else {
			$row['typeclass'] = "other";
		}
		$row['name'] = preg_replace("/【(.+?)】(.*)/","<span class='mobamas'>【$1】</span>$2",$row['name']);
		$row['tags'] = explode(",",$row['tag']);
		$row['tagquote'] = implode(",",array_map("addquote",$row['tags']));
		$vdata[$row['vno']] = $row;
	}
}

for($i=0;$i<count($data);$i++){
	$data[$i]['visible'] = isset($vdata[$data[$i]['vno']]);
	if($data[$i]['visible']) {
		$data[$i] = array_merge($data[$i],$vdata[$data[$i]['vno']]);
		$datanum++;
	}
}

#------------表示-----------------------
echo <<<EOM
<div id="layer"></div>

EOM;

echo <<<EOM
<div id="spnavi">
<div class="naviItem" id="filter">絞り込み</div>
<div class="naviItem" id="pagetop">一番上へ</div>
</div>
EOM;

echo <<<EOM
<section id="tagedit">
タグ編集: <span id="vno_tagedit">XXXXXX</span>番地<br><br>
<form action="tagedit.php" method="post" target="_blank" id="tageditform">
</form>
<div id="tageditclose"><a href="javascript:tageditclose();"  class='linkbutton'>×</a></div>
</section>

EOM;

$isreverse = $reverse ? "checked" : "";
$isnotreverse = $reverse ? "" : "checked";

$isor = $operator == "OR" ? "checked" : "";
$isand = $operator == "AND" ? "checked" : "";

echo <<<EOM
<section id="input">

<div id="input_content">

<div class="box category">役職</div>

<div class="box button">
<label><div class="chkbox"><input type="checkbox" name="jobvil" value="vilside" id="vilside" checked><b>村陣営</b></div></label>
<label><div class="chkbox"><input type="checkbox" name="job" value="vil" data-side="vil" checked>村人</div></label>
<label><div class="chkbox"><input type="checkbox" name="job" value="ura" data-side="vil" checked>占い師</div></label>
<label><div class="chkbox"><input type="checkbox" name="job" value="nec" data-side="vil" checked>霊能者</div></label>
<div class="blank">　</div>
<label><div class="chkbox"><input type="checkbox" name="job" value="bgd" data-side="vil" checked>狩人</div></label>
<label><div class="chkbox"><input type="checkbox" name="job" value="fre" data-side="vil" checked>共有者</div></label>
<label><div class="chkbox"><input type="checkbox" name="job" value="cat" data-side="vil" checked>猫又</div></label>
<label><div class="chkbox"><input type="checkbox" name="jobwlf" value="wlfside" id="wlfside" checked><b>狼陣営</b></div></label>
<label><div class="chkbox"><input type="checkbox" name="job" value="wlf" data-side="wlf" checked>人狼</div></label>
<label><div class="chkbox"><input type="checkbox" name="job" value="mad" data-side="wlf" checked>狂人</div></label>
<div class="blank">　</div>
<label><div class="chkbox"><input type="checkbox" name="jobfox" value="foxside" id="foxside" checked><b>狐陣営</b></div></label>
<label><div class="chkbox"><input type="checkbox" name="job" value="fox" data-side="fox" checked>妖狐</div></label>
<label><div class="chkbox"><input type="checkbox" name="job" value="imo" data-side="fox" checked>背徳者</div></label>
<div class="blank">　</div>
</div>

<div class="box category">結果</div>

<div class="box button">
<label><div class="chkbox"><input type="checkbox" name="result" value="win" checked>勝利</div></label>
<label><div class="chkbox"><input type="checkbox" name="result" value="draw" checked>引き分け</div></label>
<label><div class="chkbox"><input type="checkbox" name="result" value="lose" checked>敗北</div></label>
</div>

<div class="box category">人数</div>

<div class="box button">
<div class="blank">最小</div><div class="blank"><input type="number" name="nummin" id="nummin" value="8"></div><div class="blank">最大</div><div class="blank"><input type="number" name="nummax" id="nummax" value="30"></div>
</div>

<div class="box category">配役</div>

<div class="box button">
<label><div class="chkbox"><input type="checkbox" name="type" value="normal" checked>普通</div></label>
<label><div class="chkbox"><input type="checkbox" name="type" value="addjob" checked>役職増</div></label>
<label><div class="chkbox"><input type="checkbox" name="type" value="addfox" checked>妖狐増</div></label>
<label><div class="chkbox"><input type="checkbox" name="type" value="addura" checked>占い増</div></label>
<label><div class="chkbox"><input type="checkbox" name="type" value="deathnote" checked>死神手帳</div></label>
<label><div class="chkbox"><input type="checkbox" name="type" value="devil" checked>デビトリ</div></label>
<label><div class="chkbox"><input type="checkbox" name="type" value="deathdevil" checked>デビ手帳</div></label>
<label><div class="chkbox"><input type="checkbox" name="type" value="other" checked>その他</div></label>
</div>

<div class="box category">お気に入り</div>

<div class="box button"><label><div class="chkbox long"><input name='onlyfav' type='checkbox' id='onlyfav'>お気に入りのみ</div></label>
</div> 

<div class="box category">表示項目</div>
<div class="box button">
EOM;

for ($i=1;$i<=18;$i++){
	echo "\t<label><div class='chkbox'><input type='checkbox' name='chk' value='{$i}' checked>".$menulist[$i-1]."</div></label>\n";
}


echo <<<EOM
</div>

</div>
</section>
EOM;

echo "<section id=\"output\">\n\n";
echo "<div id=\"damy\"></div>\n\n";

echo "<div id='space'>\n<div class='sp'>\n";
echo '<script type="text/javascript" src="http://imgj.xrea.com/xa.j?site=mobajinro.s178.xrea.com"></script><noscript><iframe height="60" width="468" frameborder="0" marginheight="0" marginwidth="0" scrolling="no" allowtransparency="true" src="http://img.xrea.com/ad_iframe.fcg?site=mobajinro.s178.xrea.com"><a href="http://img.xrea.com/ad_click.fcg?site=mobajinro.s178.xrea.com" target="_blank"><img src="http://img.xrea.com/ad_img.fcg?site=mobajinro.s178.xrea.com" border="0" alt="xreaad"></a></iframe></noscript>';
echo "</div>\n</div>\n\n";

echo <<<EOM
<div class="box textarea"><div class="textarea_inner">
<textarea name="query" id="query" placeholder="CN:しまむー&#13;&#10;HN:島村卯月&#13;&#10;trip:◆GNBRMS/udk&#13;&#10;のように入力。&#13;&#10;trip:は省略可">{$word_disp}</textarea>
</div></div>

<div class="box button"><div class="button_inner">
<div class="radiogroop"><label><div class="radio"><input type="radio" name="reverse" value="off" {$isnotreverse}>旧→新</div></label><label><div class="radio"><input type="radio" name="reverse" value="on" {$isreverse}>新→旧</div></label></div><div class="radiogroop"><label><div class="radio"><input type="radio" name="operator" value="OR" {$isor}>OR</div></label><label><div class="radio"><input type="radio" name="operator" value="AND" {$isand}>AND</div></label></div>
</div></div>


<div class="box button"><div class="button_inner">
<div class="submit_button_damy">　</div><div class="submit_button">検索</div>
</div></div>
EOM;

echo "<div id=''>";
if($debug) echo "sql文:".$sql2."<br/>\n";
echo "<a target='_blank' href='getnewlog.php'>直近ログの取り込み</a><br><br>";
echo "検索結果:{$datanum}件<br/>\n";
echo "</div>";

echo "<div id='info'>\n\n";

echo "<div id='txtarea'>\n</div>\n";

echo "</div>\n\n";

echo "<div id=\"navi\">\n";

echo "<a href='javascript:void(0)' onclick='javascript:disp(1);'  class='linkbutton'>簡易</a>\n";
echo "<a href='javascript:void(0)' onclick='javascript:disp(2);'  class='linkbutton'>標準</a>\n";
echo "<a href='javascript:void(0)' onclick='javascript:disp(3);'  class='linkbutton'>詳細</a>\n";
echo "<a href='javascript:void(0)' onclick='javascript:$(\"#custom\").toggle();' class='linkbutton'>カスタム</a><br>\n";
echo "</div>";

echo "<div id='table'>";
echo "<table id='resulttable' style='display:none;'>\n";


if(isset($data)) $data = array_merge($menu,$data);
if(! isset($data)) echo "データがないみたいです。";

$datanum = count($data);
$cnlist = array();

for ($i=0;$i<$datanum;$i++){
	
	$cnt = $reverse ? $datanum-$i : $i;
	$cnt = $i==0 ? 0 : $cnt;

	if(! $data[$cnt]['visible']) continue;
	
	if (isset($typeclass[$data[$cnt]['type']])) {
		$typetmp = $typeclass[$data[$cnt]['type']];
	} else {
		$typetmp = "other";
	}
	if($cnt==0){
		echo "<tr class='menu'>\n";
	} else {
		echo "<tr ";
		echo "class='";
		echo "job_".$jobclass[$data[$cnt]['job']];
		echo " result_".$resultclass[$data[$cnt]['result']];
		echo " num_".$data[$cnt]['num'];
		echo " type_".$data[$cnt]['typeclass'];
		if($data[$cnt]["day"] == "生存"){
			echo " alive";
		}
		if($data[$cnt]["reason"] == "突然死"){
			echo " sudden";
		}
		echo "' data-vno='{$data[$cnt]['vno']}'";
		echo ">\n";

		if( ! array_key_exists($data[$cnt]['cn'],$cnlist) ){
			$cnlist[$data[$cnt]['cn']] = 0;
		}
		$cnlist[ $data[$cnt]['cn'] ] += 1;
	}
	if($i==0){
		echo "\t<td class='td_1'>{$data[$cnt]['vno']}</td>\n";
	} else {
		echo "\t<td class='td_1'><a class='link_tag' href='http://jinrou.dip.jp/~jinrou/kako/{$data[$cnt]['vno']}.html' target='_blank'>{$data[$cnt]['vno']}</a></td>\n";
	}
	echo "\t<td class='td_2'>{$data[$cnt]['name']}</td>\n";
	echo "\t<td class='td_3'>{$data[$cnt]['cn']}</td>\n";
	echo "\t<td class='td_4'>{$data[$cnt]['hn']}</td>\n";
	echo "\t<td class='td_5'>{$data[$cnt]['trip']}</td>\n";
	echo "\t<td class='td_6 {$jobclass[$data[$cnt]['job']]}'>{$data[$cnt]['job']}</td>\n";
	echo "\t<td class='td_7'>{$data[$cnt]['result']}</td>\n";
	echo "\t<td class='td_8'>{$data[$cnt]['day']}</td>\n";
	echo "\t<td class='td_9'>{$data[$cnt]['reason']}</td>\n";
	echo "\t<td class='td_10'>{$data[$cnt]['num']}</td>\n";
	echo "\t<td class='td_11'>{$data[$cnt]['type']}</td>\n";
	echo "\t<td class='td_12'>{$data[$cnt]['enddate']}</td>\n";
	echo "\t<td class='td_13'>{$data[$cnt]['win']}</td>\n";
	echo "\t<td class='td_14'>{$data[$cnt]['time']}</td>\n";
	echo "\t<td class='td_15'>{$data[$cnt]['npcjob']}</td>\n";
	echo "\t<td class='td_16'>{$data[$cnt]['bikou1']}</td>\n";
	echo "\t<td class='td_17'>{$data[$cnt]['bikou2']}</td>\n";
	echo "\t<td class='td_18'>";
	if($i==0){
		echo "タグ";
	}
	else {
		if($data[$cnt]['tag'] != "") {
			foreach ($data[$cnt]['tags'] as $tag){
				echo "<a class='link_tag' href='result.php?query=tag:{$tag}%0D%0A◆_shonichi_&reverse={$reverse_str}'>{$tag}</a>";
			}
		}
		echo "<a class='link_tag' href='javascript:void(0);' onclick='tagedit(event,{$data[$cnt]['vno']},{$data[$cnt]['tagquote']});'>編集</a>";
	}
	echo "</td>\n";
	echo "\t<td class='td_19' data-vno='{$data[$cnt]['vno']}'></td>\n";	
	if($debug) echo "\t<td class='td_de'><a href='update.php?vno={$data[$cnt]['vno']}&cn={$data[$cnt]['cn']}' target='_blank'>編集</a></td>\n";
	echo "</tr>\n";
}
echo "</table>";

echo "</div>";

arsort($cnlist,SORT_NUMERIC);
$cnlist["damy"] = 0;

$cnlist2 = array();
$tmp_f = 0;
$tmp_k = array();
$tmp_v = 0;
foreach ($cnlist as $key => $value) {
	if($tmp_v != $value and $tmp_f == 1){
		sort($tmp_k);
		$cnlist2[implode(" ",$tmp_k)] = $tmp_v;
		$tmp_k = array();
	}
	$tmp_k[] = "{$key}";
	$tmp_v = $value;
	$tmp_f = 1;
}
echo "<table id='cnlist'>\n";
echo "<tr><td>CN</td><td>使用回数</td></tr>\n";
foreach ($cnlist2 as $key => $value) {
	echo "<tr><td>{$key}</td><td>{$value}</td></tr>\n";
}
echo "</table>";
echo "</section>";
$mysqli->close();
?>
<div id="damybottom"></div>
</body></html>
