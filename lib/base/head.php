<?php
if(empty($title)){
    $title = 'The best reviews on the web!';
}

$_SESSION['token'] = uniqid();

$head = '
<!DOCTYPE HTML>
<html>
    <head>' . <<<tmp

               <!--
               _=====_                               _=====_
              / _____ \                             / _____ \
            +.-'_____'-.---------------------------.-'_____'-.+
           /   |     |  '.        Forty2         .'  |  _  |   \
          / ___| /|\ |___ \                     / ___| /_\ |___ \
         / |      |      | ;  __           _   ; | _         _ | ;
         | | < --   -- > | | |__|         |_:> | ||_|       (_)| |
         | |___   |   ___| ; MADE          BY  ; |___       ___| ;
         |\    | \|/ |    /  _Forty2 Reviews_   \    | (X) |    /|
         | \   |_____|  .','" "', |___|  ,'" "', '.  |_____|  .' |
         |  '-.______.-' /       \      /       \  '-._____.-'   |
         |               |       |------|       |                |
         |              /\       /      \       /\               |
         |             /  '.___.'        '.___.'  \              |
         |            /                            \             |
          \          /                              \           /
           \________/                                \_________/
           -->
tmp;
$head .= '
        <!-- META -->
        <meta name="theme-color" content="#525252">
        <meta charset="utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta content="Forty2 Reviews" name="description"/>
        <meta content="Forty2 Reviews" name="author"/>

        <link rel="shortcut icon" href="http://forty2reviews.com/lib/images/favicon.png"/>
        <title>'.$title.' - forty2reviews</title>

        <!-- CSS -->
        <link rel="stylesheet" type="text/css" href="'.$startUrl.'lib/styling/style.css">
        <link rel="stylesheet" type="text/css" href="'.$startUrl.'lib/styling/animate.css">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/themes/smoothness/jquery-ui.css" />
        <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet" type="text/css">

        <!-- Token -->
        <script>
            var token = "'.$_SESSION['token'].'";
        </script>

        <!-- JS -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script type="text/javascript" src="'.$startUrl.'lib/js/jQuery.horizontalScroll.js"></script>
        <script src="http://forty2reviews.com/lib/js/jQuery.main.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>

        <!-- notify sound -->
        <audio id="audiotag1" preload="auto">
          <source src="http://forty2reviews.com/lib/sound/notification.wav"  type="audio/wav">
        </audio>

        <script>
            $( document ).ready(function(){
                var width = $("#widthGetter > section").length * 322;
                $("#widthGetter").css("width", width, "px");
            });
            $(".headnews").on("mousewheel", function(event) {
                console.log(event.deltaX, event.deltaY, event.deltaFactor);
            });
        </script>

    </head>
    <body>
    <!-- Overlay -->
    <div id="page-overlay">

    </div>
    <div id="page-popout-div">
        <a rel="close-popout" class="btn-popout-close btn-cursor-enabled"><i class="fa fa-times"></i></a>
        <div id="page-popout-div-content"></div>
    </div>
    <!---wrapper/head-->
    <div id="wrapper">
        <div id="heading">
            <div class="container">
                <a href="'.$startUrl.'"><h1>forty2reviews <i class="fa fa-gamepad animated"></i></h1></a>
                <div class="headerButton '.($_GET['page1'] == "games" ? "active":'').'">
                    <a href="'.$startUrl.'games">Games</a>
                </div>
                <div class="headerButton '.($_GET['page1'] == "animu" ? "active":'').'">
                    <a href="'.$startUrl.'animu">Animu</a>
                </div>
                ';
                if($f2user->get_loggedIn()) {
                    $head .= '
                    <div class="right headerButton" id="user-setting">
                        <a class="username-large" id="cog-click">'.$f2user->get_username()['content'].' <div id="liveMessageBox" class=""></div> </a>
                        <div id="popup-cog">
                            <a><section><i class="fa fa-envelope"></i> Inbox</section></a>
                            <a href="'.$startUrl.'user/profile/'.$f2user->get_username()['content'].'"><section><i class="fa fa-user"></i> Profile</section></a>
                            <a rel="config"><section><i class="fa fa-cog"></i> Settings</section></a>
                            <a rel="logout"><section><i class="fa fa-sign-out"></i> Log out</section></a>
                        </div>
                    </div>
                    <div class="right headerButton">
                    </div>';
                }else {
                    $head .= '

                    <div class="right headerButton">
                        <a rel="login"><i class="fa fa-sign-in"></i> Sign in</a>
                    </div>
                    <div class="right headerButton">
                    <a rel="register"><i class="fa fa-user-plus"></i> Register</a>
                    </div>
                    <div class="right headerButton">
                    </div>';
                }
if(!empty($_GET['page2'])){
    $quote = '<p><a href="'.$startUrl.$_GET['page1'].'">'.$_GET['page1'].'</a></p><p><i class="fa fa-angle-double-right"></i> <a href="'.$startUrl.$_GET['page1'].'/'.$_GET['page2'].'">'.$_GET['page2'].''.(!empty($crumbName)? $crumbName: "").'</a></p>';
    if($_GET['page3']){
        $quote .= '<p><i class="fa fa-angle-double-right"></i> <a href="'.$startUrl.$_GET['page1'].'/'.$_GET['page2'].'/'.urlencode($_GET['page3']).'">'.$_GET['page3'].''.(!empty($crumbName2)? $crumbName2: "").'</a></p>';
        if($_GET['page4']){
            $quote .= ' <p><i class="fa fa-angle-double-right"></i> <a href="'.$startUrl.$_GET['page1'].'/'.$_GET['page2'].'/'.urlencode($_GET['page3']).'/'.$_GET['page4'].'">'.$_GET['page4'].'</a></p>';
        }
    }
}else{
    $quote = '<p>Quality reviews made by some guys in school</p>';
}
$head .= '
            </div>
        </div>
        <div id="quote">
            <div class="container">
                '.$quote.'
            </div>
        </div>
        ';
        // Display messages
        if(isset($error)) {
            $head .= "<div class='message error'><h3>$error</h3></div>";
        }
        if(isset($success)) {
            $head .= "<div class='message succes'><h3>$success</h3></div>";
        }
        if(isset($warning)) {
            $head .= "<div class='message warning'><h3>$warning</h3></div>";
        }
$head .='
        <div class="body">
            <div class="container">
';

echo $head;