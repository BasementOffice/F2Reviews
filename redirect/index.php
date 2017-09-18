<?php
header("Status: 307 Moved Permanently");
if(isset($_GET['page1']) && $_GET['page1'] != ''){
    if(isset($_GET['page2']) && $_GET['page2'] != ''){
        header('Location: http://forty2reviews.com/'.$_GET['page1'].'/'.$_GET['page2'], true, '307');
        exit;
    }
    header('Location: http://forty2reviews.com/'.$_GET['page1'], true, '307');
    exit;
}
header('Location: http://forty2reviews.com/', true, '307');
exit;
