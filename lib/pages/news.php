<?php
/****************
News page:
 * Comments
 * News text
 * Op info
 ****************/
$body .= '';
if(is_numeric($_GET['page2'])) {
    $_tmp = $f2news->get_news($_GET['page2']);
    if($_tmp['status'] == FALSE) {
        $body = '
            <div class="row">
                <div class="col-4">
                    <br>
                </div>
                <div class="col-4">
                    <div class="message error"><h3>' . $_tmp['message'] . '</h3></div>
                </div>
                <div class="col-4">
                    <br>
                </div>
            </div>';
        $crumbName = ' - Invalid';
    }else{
        $_tmp['content']['title'] = nl2br(htmlentities($_tmp['content']['title']));
        $_tmp['content']['news'] = nl2br(ArticleBBHandler($_tmp['content']['news'])); // BB Parser
        $tmp = new DateTime($_tmp['content']['created_on']);
        $crumbName = ' - ' . $_tmp['content']['title'];
        $title = $_tmp['content']['title'];

        $body .= '
        <div id="newsid" nid="' . $_GET['page2'] . '" style="display:none;"></div>
        <div class="row">
            <div class="col-12">
                <h2 class="itemname-large">'.$_tmp['content']['title'].'</h2>
            </div>
        </div>
        <div class="row newsCols">
            <div class="col-8">
                <p>'.$_tmp['content']['news'].'</p>
            </div>
            <div class="col-4">
                <p>Posted by <a class="username-small" href="'.$config['start_url'].'user/profile/'.$_tmp['content']['username'].'">'.$_tmp['content']['username'].'</a></p>
                <section>'.$f2user->get_rankTitle($f2user->get_userid($_tmp['content']['username'])['content'])['content'].'</section>
                <section>'.$_tmp['content']['user_rank'].'</section>
                <img class="profilePics" src="'.$f2user->get_useravatar($f2user->get_userid($_tmp['content']['username'])['content'])['content'].'">
                <section>'.$f2backend -> timePassed($tmp->format("d-m-Y H:i:s")).'</section>
            </div>
        </div>';

        $body .= '</div></div><div class="body"><div class="container">';
        if($f2user->get_loggedIn() && !$f2user->get_isBanned()['status']) {
            $body .= '
            <div class="row">
                <div class="commentHolder col-12"> 
                    <form  class="" id="news-comment-add">
                        <img class="profileSmallPics" src="'.$f2user->get_useravatar($f2user->get_userid()['content'])['content'].'">
                        <input type="hidden" name="action" value="add_comment" />
                        <input type="hidden" name="news_id" value="'.$_tmp['content']['id'].'" />
                        <textarea class="" name="comment" placeholder="Tell others what you think"></textarea>
                        <a href="#" rel="news-comment-submit">Submit</a>
                    </form>
                </div>
            </div>
        ';
        }
        $body .= '<div class="row"><div id="newsComments" class="commentHolder col-12"></div></div>';
    }
}else switch ($_GET['page2']){
    case 'view': {
        $body = 'Wat jij denk?';
    break;}

    default: {
        $body .= $head_news;
    break;}
}
$body .= '';