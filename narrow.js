var favlist = [];

mflag = false
offsetX = 0
offsetY = 0

String.prototype.test = function(word){
	return this.indexOf(word) !== -1
}

Array.prototype.test = function(word){
	return this.indexOf(word) !== -1
}

Number.prototype.pad = function(digit){
	return ("          "+this).substr(-digit);
}

String.prototype.pad = function(digit){
	return ("          "+this).substr(-digit);
}

function disp(mode){
	var preset= [
		[],
		[1,1,1,0,0,1,0,0,0,1,0,0,0,0,0,0,0,0],
		[1,1,1,1,1,1,1,0,0,1,0,0,0,0,0,1,1,1],
		[1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1]
	]
	var displist = [];

	if(mode){
		if(mode=="load"){
			if(localStorage.displist){
				if(localStorage.displist.slice(0,1) == "["){
					displist = JSON.parse(localStorage.displist).map((i) => {return +i})
				} else {
					localStorage.removeItem(displist)
					displist = preset[2]
				}
			} else {
				displist = preset[2]
			}
		} else {
			displist = preset[mode]
		} 
	} else {
		$("input[name=chk]").each(function(i,v){
			displist.push($(v).prop("checked") ? 1 : 0)
		})
	}

	$("input[name=chk]").each(function(i,v){
		var isshow = displist[i] ? true : false
		$("td.td_"+(i+1)).toggle(isshow)
		$(v).prop("checked",isshow)
	})
	localStorage.displist = JSON.stringify(displist);
}

function checkToggleBySide(side){
	var ischecked = $(`#${side}side`).prop("checked")
	$(`input[data-side=${side}]`).each(function(i,e){
		$(e).prop("checked",ischecked)
		$(e).closest("div").toggleClass("selected",ischecked)
	})
	narrowTable()
}

function narrowTable() {
	console.log("burn")
	var jobs =  ["vil","ura","nec","bgd","fre","cat","wlf","mad","fox","imo","all"]
	var jobname= {
		vil:'村　人',
		ura:'占い師',
		nec:'霊能者',
		bgd:'狩　人',
		fre:'共有者',
		cat:'猫　又',
		wlf:'人　狼',
		mad:'狂　人',
		fox:'妖　狐',
		imo:'背徳者',
		all:'総　計'
    }
	var beastjob = ["wlf","mad","fox","imo"]

	var isshow = []

	for (let key of ["job","result","type"]) {
		$(`input[name=${key}]:checked`).each(function(i,v){
			isshow.push($(v).val())
		})
	};

	var nummin = $("#nummin").val() -0 ;
	var nummax = $("#nummax").val() -0 ;
    var logs = []

	$("#resulttable tr").not(".menu").each(function(i,e){
		var cl = $(e).attr("class").split(" ")
		var job = cl[0].split("_")[1]
		var result = cl[1].split("_")[1]
		var num = cl[2].split("_")[1]
		var type = cl[3].split("_")[1]
		var isnotfav = ($("#onlyfav").prop("checked") && cl.includes("notfav"))
		if(isshow.includes(job) && isshow.includes(result) && isshow.includes(type) && num >=nummin && num <= nummax && !isnotfav){
			$(e).show()
			logs.push({job:job, result:result})
		} else {
			$(e).hide()
		}
	})

	var join = logs.length
	var beast = 0;
	var txt = ""

	for(let job of jobs){
		if(job!="all"){
			var logs_job = logs.filter(function(log){return log.job == job})
		} else {
			var logs_job = logs
		}

		var battle = logs_job.length
		var win = logs_job.filter(function(log){return log.result == "win"}).length
		var draw = logs_job.filter(function(log){return log.result == "draw"}).length
		var lose = logs_job.filter(function(log){return log.result == "lose"}).length

		if(beastjob.includes(job)) beast += battle;

		var win_per = (battle-draw) ? (win * 100 / (battle-draw)).toFixed(1) + "%" : "-";
		var job_per = (battle * 100 / join).toFixed(1) + "%";

		txt += `${jobname[job]}(${job_per.pad(6)})${battle.pad(4)}戦${win.pad(3)}勝${draw.pad(3)}分${lose.pad(3)}敗 勝率${win_per.pad(6)}\n`;
	}

	if(/reverse=on/.test(location.search)){
		var newest100 = logs.slice(0,100)
	} else {
		var newest100 = logs.slice(-100)
	}

	var win = newest100.filter(function(log){return log.result == "win"}).length
	var draw = newest100.filter(function(log){return log.result == "draw"}).length
	var lose = newest100.filter(function(log){return log.result == "lose"}).length

	var all = newest100.length
	var win_per = (all-draw) ? (win * 100 / (all-draw)).toFixed(1) + "%" : "-";

	txt += `\n直近100戦 　　 100戦${win.pad(3)}勝${draw.pad(3)}分${lose.pad(3)}敗 勝率${win_per.pad(6)}\n\n`;


	var tmpbeast=0,tmpwin=0,tmplose=0,contbeast=0,contwin=0,contlose=0;
	for(let log of logs){
		tmpbeast = (beastjob.includes(log.job)) ? tmpbeast+1 : 0
		if (contbeast < tmpbeast) contbeast = tmpbeast 

		if(log.result == "win" ){
			tmpwin++
			if(contwin < tmpwin) contwin = tmpwin
			tmplose=0
		} else if(log.result == "lose"){
			tmplose++
			if(contlose < tmplose) contlose = tmplose
			tmpwin=0			
		}
	}

	var beast_per = (beast *100 / join).toFixed(1) + "%";
	txt += "人外率:"+beast_per;
	txt = txt + " 連人外:"+contbeast+"回"
	txt = txt + " 連勝:"+contwin+"回"
	txt = txt + " 連敗:"+contlose+"回"

	txt = txt + "\n"

	var alive = $("tr.alive:visible").length
	var alive_per =  (alive *100 / join).toFixed(1) + "%";
	txt = txt + "生存終了:"+alive_per

	var sudden = $("tr.sudden:visible").length
	txt = txt + " 突然死:"+sudden+"回"

	$("#txtarea").html(txt);
	console.log("finish")
}

function dispfavicon(){
	if(localStorage.favlist){
		favlist = JSON.parse(localStorage.favlist)
	}
	$(".td_19").each(function(i,e){
		var vno = $(e).data("vno")
		var img
		if(vno== "村番号"){
			img = ""
		} else if(favlist.indexOf(vno) >= 0){
			img = "<img class='fav' data-vno='"+vno+"' src='img/fav.png'>"
		} else {
			img = "<img class='notfav' data-vno='"+vno+"' src='img/notfav.png'>"
			$(e).closest("tr").addClass("notfav")
		}
		$(e).append(img)
	})
}

function addfav(vno){
	favlist.push(vno)
	favlist.sort((a,b) => {return a - b;})
	localStorage.favlist = JSON.stringify(favlist)
}

function deletefav(vno){
	var i = favlist.indexOf(vno)
	if(i >= 0){
		var a = favlist.slice(0,i)
		var b = favlist.slice(i+1)
		favlist = a.concat(b)
	}
	localStorage.favlist = JSON.stringify(favlist)
}

function togglefav(e){
	console.log(e)
	if($(e).hasClass("fav")){
		$(e).attr("src","img/notfav.png")
		$(e).removeClass("fav").addClass("notfav")
		addfav($(e).data("vno"))
	} else {
		$(e).attr("src","img/fav.png")
		$(e).removeClass("notfav").addClass("fav")
		deletefav($(e).data("vno"))
	}
}

function tagedit(e,vno){
	
	var boxX = e.pageX+10
	if (boxX > window.innerWidth-305)  { boxX = window.innerWidth-305 }
	var boxY = e.pageY+15

	$("#tagedit").css("top",boxY+"px")
	             .css("left",boxX+"px")
	             .show();
	$("#vno_tagedit").text(vno);
	$("#tageditform").empty();
	for(i=2; i<arguments.length; i++){
		if(arguments[i]=="") continue;
		var no = i-2;
		$("#tageditform").append("<span>【"+arguments[i]+"】</span> <label>削除:")
		             .append('<input type="checkbox" name="del'+no+'"></label>')
		             .append('<input type="hidden" name="tag'+no+'" value="'+arguments[i]+'">')
		             .append('<br>');
	}
	$("#tageditform").append('<textarea name="tagadd" rows="3" cols="30"  placeholder="追加したいタグを入力(行区切り)">')
	                 .append('</textarea><br><br>');
	$("#tageditform").append('<input type="hidden" name="vno" value="'+vno+'">');
	$("#tageditform").append('<input type="submit">');
	$("#tageditform").submit(function(){
		window.setTimeout(function(){
			location.reload()
		},2000);
		return true;
	});
}

function tageditclose(){
	$("#tagedit").hide();
}

function mdown(e){
	$("html").on("mousemove",mmove);
	$("#tagedit").on("mouseup",mup);
	offsetX = e.pageX - parseInt( $("#tagedit").css("left") )
	offsetY = e.pageY - parseInt( $("#tagedit").css("top") )
}

function mmove(e){
	$("#tagedit").css("left",e.pageX-offsetX+"px")
	$("#tagedit").css("top",e.pageY-offsetY+"px")
}

function mup(e){
	$("html").off("mousemove")
	$("#tagedit").off("mouseup")
}

function toggleRadio(){
	$("input[type=radio]").each(function(i,chk){
		$(chk).parent().toggleClass("selected",$(chk).prop("checked"))
	})		
}

$(document).ready(function(){
	disp("load");
	narrowTable();
	$("#resulttable").show()
	$("#filter").click(function(){
		$("#input").toggle()
	})

	$("#vilside").on("change",function(){
		checkToggleBySide("vil");
	});
	$("#wlfside").on("change",function(){
		checkToggleBySide("wlf");
	});
	$("#foxside").on("change",function(){
		checkToggleBySide("fox");
	});
	$("#input input").on("change",function(){
		narrowTable()
		disp()
	});
	$("#tagedit").on("mousedown",mdown);
	$("#themebutton").on("click",function(){
		togglecss();
	});
	$('#pagetop').click(function () {
        $("html,body").animate({scrollTop:0},"300");
    });
	$(".submit_button").on("click",function(){
		var form = document.createElement('form');
		form.action = 'result.php';
		form.method = 'get';
		var input = document.createElement("textarea");
		input.name = "query";
		input.value = $("#query").val();
		form.appendChild(input);
		var input = document.createElement("input");
		input.name = "operator";
		input.value = $('input[name=operator]:checked').val() == 'OR' ? "OR" : "AND";
		form.appendChild(input);
		var input = document.createElement("input");
		input.name = "reverse";
		input.value = $('input[name=reverse]:checked').val() == 'on' ? "on" : "off";
		form.appendChild(input);
		document.body.appendChild(form);
		form.submit();
	})


	$("#input input[type=checkbox]:checked").each(function(i,chk){
		$(chk).parent().toggleClass("selected",$(chk).checked)
	})
	$("#input input[type=checkbox]").on("change",function(){
		$(this).parent().toggleClass("selected",$(this).prop('checked'))
	})

	toggleRadio()
	$("input[type=radio]").click(function(){
		toggleRadio()
	})	

	$("#fook").click(function(){
		$("#input_content").slideToggle()
	})
    dispfavicon();
    $(".td_19 img").click(function(){
		if($(this).hasClass("fav")){
			$(this).attr("src","img/notfav.png")
			$(this).removeClass("fav").addClass("notfav")
			deletefav($(this).data("vno"))
			$(this).closest("tr").addClass("notfav")
		} else {
			$(this).attr("src","img/fav.png")
			$(this).removeClass("notfav").addClass("fav")
			addfav($(this).data("vno"))
			$(this).closest("tr").removeClass("notfav")
		}
    })
});