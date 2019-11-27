<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Record audio using web browser</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </head>
  <body>
	<div class="container">
		<div class="row">
			<div class="card mt-5 col-md-12">
				<div class="card-header">
					<h1>Record audio using web browser(.js)</h1>
				</div>
				<div class="card-body text-center">
					<div id="controls">
						<div id="formats">Format: start recording to see sample rate</div>
						<h3>Recordings</h3>
						
						<audio controls id="player" src="" type="audio/wav"></audio> 
					</div>
					<div class="card-body">
						You are saying : <b><span class="result_sp text-primary"></span></b></br>
					</div>
				</div>
			</div>
		</div>
	</div>
    
  	<script src="recorder.js"></script>
  	<script>
		bool = false;
		start = false;
		var uploading = false;
	
		URL = window.URL || window.webkitURL;
		var gumStream;
		var rec; 
		var input;  
		var AudioContext = window.AudioContext || window.webkitAudioContext;
		var audioContext 

		function startRecording() {
			if(!uploading){
				var constraints = { audio: true, video: false }
				
				navigator.mediaDevices.getUserMedia(constraints).then(function(stream) {
					audioContext = new AudioContext();
					document.getElementById("formats").innerHTML="Format: 1 channel pcm @ " + audioContext.sampleRate / 1000 + "kHz"
					gumStream = stream;
					input = audioContext.createMediaStreamSource(stream);
					rec = new Recorder(input,{numChannels:1})
					rec.record();
				}).catch(function(err){ });
			}
		}
		
		function stopRecording() {
			rec.stop();
			gumStream.getAudioTracks()[0].stop();
			if(gumStream.getAudioTracks().length > 0)
			{
				uploading = true;
				rec.exportWAV(uploadSound);
				rec.exportWAV(addToPlayer);
			}else{
				uploading = false;
				console.log("No sound detected");
			}
		}
		
		function addToPlayer(blob){
			$("#player").attr("src", URL.createObjectURL(blob));
		}

		function uploadSound(blob){
			var reader = new FileReader();
			reader.readAsArrayBuffer(blob);
			reader.onloadend  = function(evt){
				xhr = new XMLHttpRequest();
				xhr.open("POST", "wb.php?name=" + "audioFile.wav", true);
				
				XMLHttpRequest.prototype.mySendAsBinary = function(text){
					var ui8a = new Uint8Array(new Int8Array(text));
					if(typeof window.Blob == "function")
					{
						blob = new Blob([ui8a]);
					}else{
						var bb = new (window.MozBlobBuilder || window.WebKitBlobBuilder || window.BlobBuilder)();
						bb.append(ui8a);
						blob = bb.getBlob();
					}
					
					this.send(blob);
				}
				
				var eventSource = xhr.upload || xhr;
				eventSource.addEventListener("progress", function(e) {
					var position = e.position || e.loaded;
					var total = e.totalSize || e.total;
					var percentage = Math.round((position/total)*100);
				});
			
				xhr.onreadystatechange = function()
				{
					if(this.readyState == 4 && this.status == 200)
					{
						console.log("Upload done!");
						
						output = this.responseText;
						console.log(output);
						if((output) != ""){
							$(".result_sp").html(this.responseText).show();
							
							switch(output.replace(/\r?\n|\r/g, "").toLowerCase()){
								case "i am a announcement":
									document.getElementById("ima_announcement").click();
								break;
								case "i am a documentation":
									document.getElementById("ima_documentation").click();
								break;
								case "i am a select teacher":
									var el = document.getElementById("select_teacher");
									var event = new MouseEvent('mousedown'); 
									el.dispatchEvent(event);
								break;
								case "i am a select competition":
									document.getElementById("select_competition").click();
								break;
							}
						}else{
							result = "Nothing math with library"
							console.log(result);
							$(".result_sp").html(result).show();
						}
						
						uploading = false;
					}
				};
				
				xhr.mySendAsBinary(evt.target.result);
			};
		}
		
		function pre_upload(){
			var inp = document.getElementById('attach');
			for (var i = 0; i < inp.files.length; ++i) {
				upload("attach", i);
			}
		}
		
	var audioContext1 = null;
	var meter = null;
	var rafID = null;
	var mediaStreamSource = null;

	window.onload = function() {
		window.AudioContext = window.AudioContext || window.webkitAudioContext;
		
		navigator.mediaDevices.getUserMedia({
			audio: {
				"mandatory": {
					"googEchoCancellation": "false",
					"googAutoGainControl": "false",
					"googNoiseSuppression": "false",
					"googHighpassFilter": "false"
				},
				"optional": []
			}, 
			video: false
		}).then(function(stream){
			audioContext1 = new AudioContext();
			mediaStreamSource = audioContext1.createMediaStreamSource(stream);
			meter = createAudioMeter(audioContext1);
			mediaStreamSource.connect(meter);
			onLevelChange();
		});
	}

	function onMicrophoneDenied() {
		alert('Stream generation failed.');
	}
	
	function onMicrophoneGranted(stream) {
		mediaStreamSource = audioContext1.createMediaStreamSource(stream);
		meter = createAudioMeter(audioContext1);
		mediaStreamSource.connect(meter);
		onLevelChange();
	}
	
	recording = false;
	nr = 0;

	function onLevelChange(time) {
		rafID = window.requestAnimationFrame(onLevelChange);
		
		if (navigator.userAgent.search("Safari") >= 0 && navigator.userAgent.search("Chrome") < 0) 
		{
			checkVol = meter.volume > 0.05;
		}else{    
			checkVol = meter.volume > 0.015;
		}
		
		if(checkVol){
			nr = 0;
		
			if(!recording){
				recording = true;
				console.log("start");
				startRecording();
			}
		}else{
			if(recording){
				nr += 1;
				if(nr > 50){
					recording = false;
					console.log("stop");
					stopRecording();
					nr = 0;
				}
			}
		}
	}

	function createAudioMeter(audioContextx,clipLevel,averaging,clipLag) {
		var processor = audioContextx.createScriptProcessor(512);
		processor.onaudioprocess = volumeAudioProcess;
		processor.clipping = false;
		processor.lastClip = 0;
		processor.volume = 0;
		processor.clipLevel = clipLevel || 0.98;
		processor.averaging = averaging || 0.95;
		processor.clipLag = clipLag || 750;
		processor.connect(audioContextx.destination);

		processor.checkClipping =
			function(){
				if (!this.clipping)
					return false;
				if ((this.lastClip + this.clipLag) < window.performance.now())
					this.clipping = false;
				return this.clipping;
			};

		processor.shutdown =
			function(){
				this.disconnect();
				this.onaudioprocess = null;
			};

		return processor;
	}

	function volumeAudioProcess( event ) {
		var buf = event.inputBuffer.getChannelData(0);
		var bufLength = buf.length;
		var sum = 0;
		var x;
		for (var i=0; i<bufLength; i++) {
			x = buf[i];
			if (Math.abs(x)>=this.clipLevel) {
				this.clipping = true;
				this.lastClip = window.performance.now();
			}
			sum += x * x;
		}
		var rms =  Math.sqrt(sum / bufLength);
		this.volume = Math.max(rms, this.volume*this.averaging);
	}
</script>
  </body>
</html>