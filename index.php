<!DOCTYPE html>
<?php 
  if(isset($_GET["dest"])){
    $dest = $_GET["dest"];
  }
  else{
    $dest = "0";
  }
?>
<html>
    <head>
        <!-- Video Chat app using peer.js -->
        <title> Video Chat </title>
        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/peerjs@0.3.20/dist/peer.min.js"></script>
         <!-- <script src="./peer.js"></script>  -->
        <link rel="stylesheet" type="text/css" href="css/index.css">
    </head>
    <body>
        <video id="other_video" autoplay="autoplay"></video>
        <video id="video" autoplay="autoplay"></video>
        <div id="main_page" class="slide">
          <div id="wrapper">
            <h1 id="big_head"> Video Chat </h1>
            <p id="author">by shd101wyy</p>
            <button id="enter_our_world_btn"> Create Room </button>
          </div>
        </div>
        <input type="text" name="chat" id="chat-box">
        <div id="text-box" style="background: white">
          
        </div>
    </body>
    <script type="text/javascript">
      $(document).ready(function(){
        var DEST = "<?php echo $dest; ?>"; 
        console.log(DEST);
        if(DEST !== "0"){
          $("#main_page").hide();
        }
        $("#enter_our_world_btn").click(function(){
          alert("Please allow browser camera access first ;)");
        })
        // $("#chat-box").hide();

        var lOCAL_MEDIA_STREAM;
        var startVideoChat;
        $("#video").hide();
        $("#other_video").hide();
        var peer = new Peer({key: 'lwjd5qra8257b9'});
        var MY_ID = "";
        peer.on('open', function(id){
            console.log("my id is " + id);
            MY_ID = id;
        });
        navigator.getUserMedia = ( navigator.getUserMedia ||
                             navigator.webkitGetUserMedia ||
                             navigator.mozGetUserMedia ||
                             navigator.msGetUserMedia);
        if (navigator.getUserMedia) {
           navigator.getUserMedia (
              // constraints
              {
                 video: true,
                 audio: true
              },
              function(localMediaStream) {
                lOCAL_MEDIA_STREAM = localMediaStream

                peer.on('connection', function(conn){ // receive connection
                    conn.on('data', function(data) {
                          try {
                            let a = JSON.parse(data);
                            console.log(a);
                            $('#text-box').append('<span>'+MY_ID+' : '+a.mess+'</span></br>');
                          }
                          catch(error) {
                            startVideoChat(data);
                          } 
                    })
                }); 

                if (DEST === '0') { // no room found
                    $("#enter_our_world_btn").unbind("click");
                    $("#enter_our_world_btn").click(function(){
                        $("#big_head").html("Room Created!");
                        $("#author").html("<br>ask your friend to go to this link ;)<br><strong>dont' close or refresh this window</strong> while waiting for your friend to connect<br><br>" + "<strong>http://localhost/videochat_week13/index.php?dest="+MY_ID+"</strong>");
                        $("#enter_our_world_btn").unbind('click');
                        $("#enter_our_world_btn").html("Copy Link");
                        $("#enter_our_world_btn").click(function() {
                          window.prompt("Copy to clipboard: Ctrl+C, Enter", "http://localhost/videochat_week13/index.php?dest="+MY_ID);
                        });
                    })
                }
                else{
                      var conn = peer.connect(DEST); // start connection
                      conn.on('open', function(){
                          // send data
                          conn.send(MY_ID);
                      })
                      startVideoChat(DEST);
                }
              },
              // errorCallback
              function(err) {
                 console.log("The following error occured: " + err);
              }
           );
        } else {
           alert("getUserMedia not supported");
        } 

        startVideoChat = function(remote_user_id){
          console.log("START VIDEO CHAT WITH " + remote_user_id);
          $("#main_page").hide();
          $("#video").show();
          $("#other_video").show();

          $("#other_video").css("position", "relative");
          $("#other_video").css("width", $(window).width());
          $("#other_video").css("height", $(window).height());

          $("#video").css("position", "relative");
          $("#video").css("width", "400px");
          $("#video").css("height", "400px");
          $("#video").css("right", "0px");
          $("#video").css("bottom", "0px");


          

          var video = document.getElementById('video');
          video.srcObject = lOCAL_MEDIA_STREAM;
          // video.volume = 0.9;
          video.muted = true;
          video.play();


          var con = peer.connect(remote_user_id);
          con.on('data', function(data){ console.log(data) });
          // con.send('HELLO WORLD');

          $("#chat-box").on('keyup', function(e) {
            if (e.keyCode == 13) {
              let mess = $("#chat-box").val();
              $("#chat-box").val('');
              $('#text-box').append('<span>'+MY_ID+' : '+ mess+'</span></br>');
              con.send(JSON.stringify({'key': 'mess', 'mess': mess}));
            }
          });

          var call = peer.call(remote_user_id, lOCAL_MEDIA_STREAM); // call to that id
          call.on('stream', function(stream) {
              // `stream` is the MediaStream of the remote peer.
              // Here you'd add it to an HTML video/canvas element.
              console.log("1 receive stream from remote user\n");
              var other_video = document.getElementById("other_video");
              other_video.srcObject = stream;
              other_video.play();
            });

          peer.on('call', function(call) {  // answer call
            // Answer the call, providing our mediaStream
            call.answer(lOCAL_MEDIA_STREAM);
            call.on('stream', function(stream) { // 接收
              // `stream` is the MediaStream of the remote peer.
              // Here you'd add it to an HTML video/canvas element.
              console.log("2 receive stream from remote user\n");
              var other_video = document.getElementById("other_video");
              other_video.srcObject = stream;
              other_video.play();
            });
            peer.on('error', function(err) { console.log("ERROR1:", err)});
          });

          peer.on('error', function(err) { console.log("ERROR2:", err)});

        
        }
        window.onbeforeunload = function() {
          peer.disconnect();
          peer.destroy();
          return;
        };
      });

    </script>
</html>