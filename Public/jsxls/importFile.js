
//var outputJSON;
var xjson = {};
var countryData = [];

var errorMsg = function (msg){
	$("#eventsTable").bootstrapTable('removeAll');
	$(".no-records-found td").html("<p style='color:red;font-weight: bold;'>"+msg+"</p>");
}

$(function() {
	var X = XLSX;
	var XW = {
		/* worker message */
		msg: "xlsx",
		/* worker scripts */
		rABS: $public+"/jsxls/xlsxworker2.js",
		norABS: $public+"/jsxls/xlsxworker1.js",
		noxfer: $public+"/jsxls/xlsxworker.js"
	};

	var rABS = typeof FileReader !== "undefined" && typeof FileReader.prototype !== "undefined" && typeof FileReader.prototype.readAsBinaryString !== "undefined";
	/*if(!rABS) {
		document.getElementsByName("userabs")[0].disabled = true;
		document.getElementsByName("userabs")[0].checked = false;
	}*/

	var use_worker = typeof Worker !== 'undefined';
	/*if(!use_worker) {
		document.getElementsByName("useworker")[0].disabled = true;
		document.getElementsByName("useworker")[0].checked = false;
	}*/

	var transferable = use_worker;
	/*if(!transferable) {
		document.getElementsByName("xferable")[0].disabled = true;
		document.getElementsByName("xferable")[0].checked = false;
	}*/

	var wtf_mode = false;

	function fixdata(data) {
		var o = "",
			l = 0,
			w = 10240;
		for (; l < data.byteLength / w; ++l) o += String.fromCharCode.apply(null, new Uint8Array(data.slice(l * w, l * w + w)));
		o += String.fromCharCode.apply(null, new Uint8Array(data.slice(l * w)));
		return o;
	}

	function ab2str(data) {
		var o = "",
			l = 0,
			w = 10240;
		for (; l < data.byteLength / w; ++l) o += String.fromCharCode.apply(null, new Uint16Array(data.slice(l * w, l * w + w)));
		o += String.fromCharCode.apply(null, new Uint16Array(data.slice(l * w)));
		return o;
	}

	function s2ab(s) {
		var b = new ArrayBuffer(s.length * 2),
			v = new Uint16Array(b);
		for (var i = 0; i != s.length; ++i) v[i] = s.charCodeAt(i);
		return [v, b];
	}

	function xw_noxfer(data, cb) {
		var worker = new Worker(XW.noxfer);
		worker.onmessage = function(e) {
			switch (e.data.t) {
				case 'ready':
					break;
				case 'e':
					console.error(e.data.d);
					break;
				case XW.msg:
					cb(JSON.parse(e.data.d));
					break;
			}
		};
		var arr = rABS ? data : btoa(fixdata(data));
		worker.postMessage({
			d: arr,
			b: rABS
		});
	}

	function xw_xfer(data, cb) {
		var worker = new Worker(rABS ? XW.rABS : XW.norABS);
		worker.onmessage = function(e) {
			switch (e.data.t) {
				case 'ready':
					break;
				case 'e':
					console.error(e.data.d);
					break;
				default:
					xx = ab2str(e.data).replace(/\n/g, "\\n").replace(/\r/g, "\\r");
					console.log("done");
					cb(JSON.parse(xx));
					break;
			}
		};
		if (rABS) {
			var val = s2ab(data);
			worker.postMessage(val[1], [val[1]]);
		} else {
			worker.postMessage(data, [data]);
		}
	}

	function xw(data, cb) {
		//transferable = document.getElementsByName("xferable")[0].checked;
		if (transferable) xw_xfer(data, cb);
		else xw_noxfer(data, cb);
	}

	/*function get_radio_value( radioName ) {
		var radios = document.getElementsByName( radioName );
		for( var i = 0; i < radios.length; i++ ) {
			if( radios[i].checked || radios.length === 1 ) {
				return radios[i].value;
			}
		}
	}*/

	function to_json(workbook) {
		var result = {};
		workbook.SheetNames.forEach(function(sheetName) {
			var roa = X.utils.sheet_to_row_object_array(workbook.Sheets[sheetName]);
			if (roa.length > 0) {
				result[sheetName] = roa;
			}
		});
		return result;
	}

	function to_csv(workbook) {
		var result = [];
		workbook.SheetNames.forEach(function(sheetName) {
			var csv = X.utils.sheet_to_csv(workbook.Sheets[sheetName]);
			if (csv.length > 0) {
				result.push("SHEET: " + sheetName);
				result.push("");
				result.push(csv);
			}
		});
		return result.join("\n");
	}

	function to_formulae(workbook) {
		var result = [];
		workbook.SheetNames.forEach(function(sheetName) {
			var formulae = X.utils.get_formulae(workbook.Sheets[sheetName]);
			if (formulae.length > 0) {
				result.push("SHEET: " + sheetName);
				result.push("");
				result.push(formulae.join("\n"));
			}
		});
		return result.join("\n");
	}

	var tarea = document.getElementById('b64data');
	/*function b64it() {
		if(typeof console !== 'undefined') console.log("onload", new Date());
		var wb = X.read(tarea.value, {type: 'base64',WTF:wtf_mode});
		process_wb(wb);
	}*/
	/*$.ajax({
	    url: "https://api.github.com/users/wenzhixin/repos?type=owner&sort=full_name&direction=asc&per_page=100&page=1",
	    type: "get",
	    dataType: "json",
	    success: function (data) {
	    	var output = JSON.stringify(data);
	    	out.innerText = output;
	                $('#eventsTable').bootstrapTable('refresh',{
	                    data: data
	                });
	    }
	});*/
	/*function process_wb(wb) {
		var output = "";
		// var format = get_radio_value("format");
		var format = 'json';
		switch(format) {
			case "json":
				output = JSON.stringify(to_json(wb), 2, 2);
				break;
			case "form":
				output = to_formulae(wb);
				break;
			default:
			output = to_csv(wb);
		}		
		if(out.innerText === undefined) out.textContent = output;
		else out.innerText = output;
		if(typeof console !== 'undefined') console.log("output", new Date());
	}*/
	function chackJson(json){	
		xjson = {};
		var msg = '';
		var exclude = ["ShipperRef","GoodsDescription","GoodsSN", "CustomNote"];
		var chack = function(name,val,j){
			if(val==undefined||$.trim(val)==''){
				if(!$.inArray(name, exclude)) {
					msg = name + "字段内容不能为空！";
					return false;
				}
			}else{			
				if(n=="Destination"){					
					var statu = false;	
					for (var z = 0; z < countryData.length; z++) {
						json[j][name] = xjson[name][j] = val = val.toUpperCase();
						if(countryData[z]["CodeId"]==val){
							statu = true;
							xjson["AgentCode"][j] = countryData[z]["CID"]=="SA"?"NX":"FF";
							json[j]["AgentCode"] = xjson["AgentCode"][j];
							break;
						}
					}
					if(!statu)msg = "Destination字段有异常！";
				}else
				if(name=="noofpieces"){
					if(isNaN(val)){	msg = name + "字段内容应该为整数！";return false;}
				}else
				if(name=="InAmt"){
					if(!$.isNumeric(val)){msg = name + "字段内容应该为数字！";return false;}
				}else
				if(name=="product"){
					val = val.toUpperCase();
					if(val!="XPS"&&val!="DOX"){msg = name + "字段内容应该为XPS、DOX！";return false;}
					json[j][name] = xjson[name][j] = val;
				}else
				if(name=="ServiceType"){
					val = val.toUpperCase();
					var amt = json[j]["InAmt"];
					if(val!="NCND"&&val!="NOR"){msg = name + "字段内容应该为NCND、NOR！";return false;}
					else if(val=="NOR"&&amt!=0){msg = name + "字段为NOR的时候NcndAmt金额为0！";return false;}
					else if(val=="NCND"&&amt==0){msg = name + "字段为NCND的时候NcndAmt金额不能为0！";return false;}
					json[j][name] = xjson[name][j] = val;
				}
				else {
					json[j][name] = xjson[name][j] = val;
				}
			}
			msg = '';
			return true;
		}
		var th = $("#eventsTable tr th");
		var cls = [];
		for (var i = 1; i < th.length; i++) {
			cls.push($(th[i]).attr("data-field"));
		}

		/*for (i = 0; i < json.length; i++) {
			xjson[i] = {};
			var v = json[i];
			for (var j = 0; j < cls.length; j++) {
				var n = cls[j];
				xjson[i][cls[j]] = v[n];
			}
		}*/
		xjson["AgentCode"] = [];
		for (i = 0; i < cls.length; i++) {
			var n = cls[i];
			if(n=="AgentCode") continue;
			xjson[n] = [];
			for (var j = 0; j < json.length; j++) {
				var v = json[j][n];
				xjson[n][j] = v;
				if(!chack(n,v,j)){
					xjson = {};
					errorMsg("第"+(j+1)+"行,"+msg);
					return false;
				}
			}//end
		}
		console.log(json, cls, xjson);
		return true;
		//console.log(JSON.stringify(xjson,2,2));
	}
	function process_wb(wb) {
		var output = to_json(wb).Sheet1;
		var table = $('#eventsTable');
		if(chackJson(output)==false)return;
		// table.bootstrapTable('destroy').bootstrapTable({
		table.bootstrapTable('load',{
			height: getHeight(),
			data: output,
			pagination: true,
			onResetView: function() {
				console.log("onload ok!");
			}
		});
		// addSaveBut();
		//outputJSON = output;
		//out.innerText = JSON.stringify(output,2,2);
	}

	var drop = document.getElementById('drop');

	function handleDrop(e) {
		e.stopPropagation();
		e.preventDefault();
		//rABS = document.getElementsByName("userabs")[0].checked;
		//use_worker = document.getElementsByName("useworker")[0].checked;
		var files = e.dataTransfer.files;
		var f = files[0]; {
			var reader = new FileReader();
			var name = f.name;
			reader.onload = function(e) {
				if (typeof console !== 'undefined') console.log("onload", new Date(), rABS, use_worker);
				var data = e.target.result;
				if (use_worker) {
					xw(data, process_wb);
				} else {
					var wb;
					if (rABS) {
						wb = X.read(data, {
							type: 'binary'
						});
					} else {
						var arr = fixdata(data);
						wb = X.read(btoa(arr), {
							type: 'base64'
						});
					}
					process_wb(wb);
				}
			};
			if (rABS) reader.readAsBinaryString(f);
			else reader.readAsArrayBuffer(f);
		}
	}

	function handleDragover(e) {
		e.stopPropagation();
		e.preventDefault();
		e.dataTransfer.dropEffect = 'copy';
	}

	if (drop.addEventListener) {
		drop.addEventListener('dragenter', handleDragover, false);
		drop.addEventListener('dragover', handleDragover, false);
		drop.addEventListener('drop', handleDrop, false);
	}


	var xlf = document.getElementById('xlf');

	function handleFile(e) {
		//rABS = document.getElementsByName("userabs")[0].checked;
		//use_worker = document.getElementsByName("useworker")[0].checked;
		var files = e.target.files;
		var f = files[0]; {
			var reader = new FileReader();
			var name = f.name;
			reader.onload = function(e) {
				if (typeof console !== 'undefined') console.log("onload", new Date(), rABS, use_worker);
				var data = e.target.result;
				if (use_worker) {
					xw(data, process_wb);
				} else {
					var wb;
					if (rABS) {
						wb = X.read(data, {
							type: 'binary'
						});
					} else {
						var arr = fixdata(data);
						wb = X.read(btoa(arr), {
							type: 'base64'
						});
					}
					process_wb(wb);
				}
			};
			if (rABS) reader.readAsBinaryString(f);
			else reader.readAsArrayBuffer(f);
		}
	}

	if (xlf.addEventListener) xlf.addEventListener('change', handleFile, false);
});