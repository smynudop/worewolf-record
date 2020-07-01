var favlist = [];

mflag = false
offsetX = 0
offsetY = 0

var logs



Number.prototype.pad = function(digit){
	return ("          "+this).substr(-digit)
}

String.prototype.pad = function(digit){
	return ("          "+this).substr(-digit)
}

String.prototype.lastpad = function(digit){
	return (this+"               ").slice(0,digit+1)
}

class Log{
	constructor(e){
		var cl = $(e).attr("class").split(" ")

		this.e = e
		this.job = cl[0].split("_")[1]
		this.result = cl[1].split("_")[1]
		this.num = cl[2].split("_")[1]
		this.type = cl[3].split("_")[1]	
		this.isfav = !(cl.includes("notfav"))
		this.alive = cl.includes("alive")
		this.sudden = cl.includes("sudden")
		this.visible = true
	}

	toggle(cond){
		let isshow = cond.job[this.job]
		          && cond.result[this.result]
		          && this.num >= cond.min
		          && this.num <= cond.max
		          && cond.type[this.type]
		          && (!cond.onlyfav || this.isfav)
        this.visible = isshow
        $(this.e).toggle(isshow)
	}
}

class Logs{
	constructor(list, job){
		this.list = list || []
		this.job = job || "総　計"
	}

	get num(){
		return this.list.length
	}

	add(log){
		this.list.push(log)
	}

	narrow(){
		this.showLog()
		this.makeSummary()

	}

	showLog(){
		var showCondition = {}

		for (let key of ["job","result","type"]) {
			showCondition[key] = {}
			$(`input[name=${key}]`).each(function(i,v){
				let name = $(v).val()
				showCondition[key][name] = $(v).prop("checked")
			})
		};
		showCondition.min = $("#nummin").val() -0 ;
		showCondition.max = $("#nummax").val() -0 ;
		showCondition.onlyfav = $("#onlyfav").prop("checked")

		for(let log of this.list){
			log.toggle(showCondition)
		}
	}

	makeSummary(){
		let visibleLogs = this.visibleLogs()

		var jobs= {
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
	    }

		var txt = ""
		var num = logs.num

		for(let key in jobs){
			let job = jobs[key]
			txt += visibleLogs.filterJob(key, job).countWinOrLose(num)
		}
		txt += visibleLogs.countWinOrLose(num)
		txt += "\n"
		txt += visibleLogs.recent(100).countWinOrLose()
		txt += "\n"

		txt += visibleLogs.countVariousData()

		$("#txtarea").html(txt);

	}

	countVariousData(){

		var num = this.num

		//人外
		var beastjob = ["wlf","mad","fox","imo"]
		var beastnum = this.list.filter((l) => beastjob.includes(l.job)).length
		var beastper = num ? (beastnum * 100 / num).toFixed(1) + "%" : "-";

		//連人外・連勝・連敗
		var contb = 0, contw = 0, contl = 0, tmpb = 0, tmpw = 0, tmpl = 0
		for(let log of this.list){
			if(beastjob.includes(log.job)){
				tmpb++
				contb = Math.max(contb,tmpb)
			} else{
				tmpb = 0
			}

			if(log.result == "win"){
				tmpw++
				contw = Math.max(contw, tmpw)
				tmpl = 0
			}
			if(log.result == "lose"){
				tmpl++
				contl = Math.max(contl, tmpl)
				tmpw = 0
			}
		}
		var alivenum = this.list.filter((l) => l.alive).length
		var aliveper = num ? (alivenum * 100 / num).toFixed(1) + "%" : "-";

		var sudden = this.list.filter((l) => l.sudden).length

		return `人外率:${beastper} 連人外:${contb} 連勝:${contw} 連敗:${contl}\n生存終了:${aliveper} 突然死:${sudden}`


	}

	filterJob(key,job){
		return new Logs(this.list.filter((l) => l.job == key), job)
	}

	recent(num){
		let l 
		if(/reverse=on/.test(location.search)){
			l = this.list.slice(0,num)
		} else {
			l = this.list.slice(-num)
		}
		return new Logs(l, "直近"+num+"戦")
	}

	countWinOrLose(num){
		let win = 0, draw = 0, lose = 0, battle = 0
		for(let log of this.list){
			switch(log.result){
				case "win":
					win++
					break
				case "draw":
					draw++
					break
				case "lose":
					lose++
					break
			}
		}
		battle = this.num.pad(4)
		win = win.pad(3)
		draw = draw.pad(3)
		lose = lose.pad(3)

		var win_per = (battle-draw) ? (win * 100 / (battle-draw)).toFixed(1) + "%" : "-";
		win_per = win_per.pad(6)

		if(num){
			var job_per = (battle * 100 / num).toFixed(1) + "%";

			job_per = job_per.pad(6)
			return  `${this.job}(${job_per})${battle}戦${win}勝${draw}分${lose}敗 勝率${win_per}\n`;		
		} else {
			let job = this.job.lastpad(10)
			return  `${job}${battle}戦${win}勝${draw}分${lose}敗 勝率${win_per}\n`;
		}

	}

	visibleLogs(){
		return new Logs(this.list.filter((l) => l.visible))
	}
}

function disp(mode){
	var preset= [
		[],
		[1,1,1,0,0,1,0,0,0,1,0,0,0,0,0,0,0,0],
		[1,1,1,1,1,1,1,0,0,1,0,0,0,0,0,1,0,0],
		[1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1]
	]
	var displist = [];

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

	$("input[name=chk]").each(function(i,v){
		var isshow = !!displist[i]
		$(".td_"+(i+1)).toggle(isshow)
		$(v).prop("checked",isshow)
		$(v).closest("div").toggleClass("selected", isshow)
	})
	localStorage.displist = JSON.stringify(displist);
}

function toggleDisp(e){
	var isshow = $(e).prop("checked")
	let i = $(e).val()
	$(".td_"+i).toggle(isshow)
}

function checkToggleBySide(side, ischecked){
	$(`input[data-side=${side}]`).each(function(i,e){
		$(e).prop("checked",ischecked)
		$(e).closest("div").toggleClass("selected",ischecked)
	})
	logs.narrow()
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

$(function(){
    dispfavicon();
	logs = new Logs()
	$("#resulttable tr").each(function(i,e){
		if(!$(e).hasClass("menu") )logs.add(new Log(e))
	})

	disp("load");
	logs.narrow();

	$("#resulttable").show()

	$("#filter").click(function(){
		$("#input").toggle()
	})

	$(".disp input").on("change", function(){
		toggleDisp(this)
	})

	$("input[name=side]").on("change",function(){
		checkToggleBySide($(this).data("side"), $(this).prop("checked"));
	});

	$(".narrow input").on("change",function(){
		logs.narrow()
	});

	$(".dispPreset").click(function(){
		let v = $(this).data("value") -0
		disp(v)
	})

	$("#tagedit").on("mousedown",mdown);

	$('#pagetop').click(function () {
        $("html,body").animate({scrollTop:0},"300");
    });

	$(".submit_button").on("click",function(){
		document.forms[1].submit();
	})

	$("#input input[type=checkbox]:checked").each(function(i,chk){
		$(chk).parent().toggleClass("selected",$(chk).prop('checked'))
	})

	$("#input input[type=checkbox]").on("change",function(){
		$(this).parent().toggleClass("selected",$(this).prop('checked'))
	})

	toggleRadio()
	$("input[type=radio]").click(function(){
		toggleRadio()
	})	


    $(".td_19 img").click(function(){
		togglefav(this)
    })
});