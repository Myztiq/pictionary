<html>
<head>

	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
	<script type="text/javascript" src="http://localhost/pictionary/jsonJQuery.js"></script>
	<script>
		var socket;
		var mouseDown = false;

		function init() {
			var host = "ws://localhost:8080/pictionary/server.php";
			try {
				socket = new WebSocket(host);
				socket.onopen = function(msg){ };
				socket.onmessage = function(msg){
					eval('var data = ' + msg.data + ';');
					for (userId in data) {
						if(data[userId].posX && data[userId].posY) {
							var color = data[userId].color;
							renderPosition(userId,  data[userId].posX, data[userId].posY, color);
						}else if(data[userId].message){
							jQuery("#log").append('<span style="color:'+data[userId]['color']+'">'+data[userId]["IP"]+"> "+data[userId]['message']+"</span><br/>");
//							console.log(data);
						}else{
//							console.log(data);
						}
					}
				};

			} catch(ex){
				console.log(ex);
			}

			$('body').bind('mousedown', function(evt){
				mouseDown = true;
			});
			$('body').bind('mouseup', function(evt){
				mouseDown = false;
			});
			$('body').bind('mousemove', function(evt){
				send(evt.clientX, evt.clientY,mouseDown);
			});
		}

		function renderPosition(u, x, y, c) {
			if ($('#'+u).length == 0) {
				$('<div class="cursor" id="' + u + '"></div>').appendTo('body');
			}
			$('#'+u).css('left', x+'px');
			$('#'+u).css('top', y+'px');
			$('#'+u).css('background', c);
		}
		
		function send(x,y,mouseDown) {
			var obj = {
				mouse:{
					posX: x,
					posY: y,
					clicked: mouseDown
				}
			};
			socket.send($.JSON.encode(obj));
		}
		
		function onkey(event){ if(event.keyCode==13){ sendText(); } }
		function sendText(){
			var txt,msg;
			txt = jQuery("#msg");
			msg = txt.val();
			if(!msg){
				alert("Message can not be empty");
				return;
			}
			txt.val("");
			txt.focus();
			try{
				var obj = {
					message:msg
				};
				socket.send($.JSON.encode(obj));
			} catch(ex){
				console.log(ex);
			}
		}


	</script>
		<style type="text/css">
		.cursor { position:absolute; left:0; top:0; width:10px; height:10px; background:lime; }
		.icon { float:left; width:10px; height:10px; margin-right:5px; }
		#console, #info { font:12px arial,helvetica,sans-serif; margin:20px; padding:20px; background:#f1f1f1; width:250px; }
		#console { background:url(http://www.easevents.com/pdacamps/kamila/funny-cat.jpg) no-repeat center center; font-size:10px; height:250px; overflow:auto; }
		#console ul { margin:0; padding:0; }
		.user { background:#fff; padding:5px; list-style-type:none; }
		h1 { font-size:20px; margin:0; }
		h2 { margin:0 0 10px 0; }
		button { position:absolute; right:0; bottom:0; font-size:20px; padding:5px 10px; margin:40px; }
	</style> 
</head>
<body onload="init()">
	<canvas id="pictionaryCanvas"></canvas>
	<div class="chatterBox">
        <div id="log"></div>
		<input id="msg" type="textbox" onkeypress="onkey(event)"/>
	</div>
</body>
</html>