<?php
/**
 * Created by PhpStorm.
 * User: Mitchel
 * Date: 3/19/15
 * Time: 11:06 AM
 */
require_once("config.cfg.php");

//------jBB--------------------
require_once($_SERVER['DOCUMENT_ROOT'] . "lib/jBBcode/Parser.php");
$cjBB = new JBBCode\Parser();
$cjBB->addCodeDefinitionSet(new JBBCode\ArticleCodeDefinitionSet());
$ajBB = new JBBCode\Parser();
$ajBB->addCodeDefinitionSet(new JBBCode\ArticleCodeDefinitionSet());
function CommentBBHandler($string = "") {
    global $cjBB;
    $cjBB->parse(htmlentities($string));
    return $cjBB->getAsHTML();
}
function ArticleBBHandler($string = "") {
    global $ajBB;
    $ajBB->parse(htmlentities($string));
    return $ajBB->getAsHTML();
}
//-----------------------------
// Create instances - session
if(!isset($_SESSION['f2user'])) {
    $_SESSION['f2user'] = new f2user();
}
if(!isset($_SESSION['f2frontend'])) {
    $_SESSION['f2frontend'] = new f2frontend();
}
if(!isset($_SESSION['f2news'])) {
    $_SESSION['f2news'] = new f2news();
}
if(!isset($_SESSION['f2backend'])) {
    $_SESSION['f2backend'] = new f2backend();
}
if(!isset($_SESSION['f2comment'])) {
    $_SESSION['f2comment'] = new f2comment();
}
if(!isset($_SESSION['f2newscomment'])) {
    $_SESSION['f2newscomment'] = new f2newscomment();
}
if(!isset($_SESSION['f2review'])) {
    $_SESSION['f2review'] = new f2review();
}
if(!isset($_SESSION['f2item'])) {
    $_SESSION['f2item'] = new f2item();
}
if(!isset($_SESSION['f2notification'])) {
    $_SESSION['f2notification'] = new f2notification();
}
$f2user         = &$_SESSION['f2user'];
$f2frontend     = &$_SESSION['f2frontend'];
$f2news         = &$_SESSION['f2news'];
$f2backend      = &$_SESSION['f2backend'];
$f2comment      = &$_SESSION['f2comment'];
$f2newscomment  = &$_SESSION['f2newscomment'];
$f2review       = &$_SESSION['f2review'];
$f2item         = &$_SESSION['f2item'];
$f2notification = &$_SESSION['f2notification'];


/**************************************
 **************Include stuff***********
 *************************************/
// News header-----------------------------------------------------------------------------
$head_news = '
<div class="row">
    <div class="headNews">
        <div style="width:auto" id="widthGetter">';
// Get news
$news = $f2news->get_latestNews(5);
if($news['status'] == FALSE) {
    // Error
    $f2backend->logError("home.php - " . $_SERVER['SCRIPT_NAME'], $news['msg'], '$_GET[\'page1\']: ' . $_GET['page1']);
}else{
    foreach($news['content'] as $news) {
        $_tmp = new DateTime($news['created_on']);
        $head_news .= '
        <section>
            <div class="image-wrapper">
                <img src="http://forty2reviews.com/lib/images/news_pics/'.(empty($news['news_pic'])?"default.jpg":$news['news_pic']).'" />
            </div>
            <div class="headNewsText cursor-enabled" rel="news-click" news="' . $news['id'] . '">
                <h2 class="username-large">' . $news['username'] .'</h2><div class="timeNews">'.$f2backend -> timePassed($_tmp->format("d-m-Y H:i:s")).'</div>
                <p>' . $news['title'] . '</p>
            </div>
        </section>
        ';
    }
}
$head_news .=            '
        </div>
    </div>

</div>';
//------------------------------------------------------------------------------------------