<?php
if(isset($_GET['error']) && $_GET['error'] != ""){
    $error_type = $_GET['error'];
    $title = "FATAL ERROR!";
    $body = "ERROR! WE DON'T HAVE AN ERROR MESSAGE FOR THAT!";
}else{
    $title = "ERROR!";
    $body = "ERROR FOR HAVING NO ERROR SET IN THE PHP VARIABLE!";
    $error_type = "?!?!?!?!?!?!";
}


if($error_type == 400){
    $title = "THAT'S WRONG!";
    $body = "BAD REQUEST!";
}else if($error_type == 401){
    $title = "GO AWAY!";
    $body = "RESPECT MY AUTHORITAH! <iframe width='600' height='315' src='//www.youtube.com/embed/gx4jn77VKlQ' frameborder='0' allowfullscreen></iframe>";
}else if($error_type == 403){
    $title = "THAT'S NOT FOR YOU!";
    $body = "IT SEEMS YOU FOUND SOMETHING YOU SHOULDN'T HAVE FOUND AT ALL!";
}else if($error_type == 404){
    $title = "WE DON'T HAVE THAT STUFF!";
    $body = "NOTHING TO SEE HERE, MOVE ALONG!";
}else if($error_type == 500){
    $title = "THE SERVER EXPLODED!";
    $body = "IT SEEMS OUR NUCLEAR POWERED SERVER EXPLODED!";
}
?>
<html><head>
    <title><?php echo $title; ?></title>
    <style type="text/css"></style></head>

<body style="margin:0px;background-color:#357e95;width:100%;">
<div style="height:auto;width:auto;color:white;margin-left:20%;margin-top:12%;font-family:Helvetica;">

    <h1 style="padding:0;margin:0; font-size:120px;">
        :(
    </h1><p style="padding:0;margin:0; font-size:44px;">
        <?php echo $body; ?>
    </p><br><p style="padding:0;margin:0; font-size:13px;">
        You can search for the error online:  ERROR_<?php echo $error_type; ?>
    </p><table border="0">
        <tbody><tr>

        </tr>
        <tr>


        </tr>
        <tr>

        </tr>
        </tbody></table>
</div>



</body></html>