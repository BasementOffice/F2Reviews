<?php
/**
 * Created by PhpStorm.
 * User: Root
 * Date: 3/25/15
 * Time: 5:34 PM
 */
// Autoload
function __autoload($className) {
    if(file_exists($_SERVER['DOCUMENT_ROOT'] . "lib/classes/" . $className . '.class.php')) {
        require ($_SERVER['DOCUMENT_ROOT'] . "lib/classes/" . $className . '.class.php');
    }else{
        require ($_SERVER['DOCUMENT_ROOT'] . "lib/" . $className . '.php');
    }
}

session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . 'lib/secret/config.cfg.php');
require_once($_SERVER['DOCUMENT_ROOT'] . 'lib/secret/include.php');
global $config;
// Check if an action has been set
if(!empty($_POST) && isset($_POST['action'])) {
    switch($_POST['action']) {
        case 'get_comments': {
            // Check if all data has been sent
            if(!isset($_POST['id'])) {$msg = 'corrupt_data';}
            if(!isset($_POST['page'])) {$_POST['page'] = 0;}
            if(!is_numeric($_POST['page']) && $_POST['page'] !== "last") {
                $_POST['page'] = 0;
            }
            // Get page count
            $commentCount = $f2comment->get_commentCount($_POST['id'])['content'];
            $pages = ceil($commentCount / $config['results_per_page']);
            if($_POST['page'] == "last") {
                $page = $pages;
            }else{
                $page = $_POST['page'];
            }
            //--------------

            $_tmp = $f2comment->get_reviewComments($_POST['id'], $page);
            if($_tmp['status'] == FALSE) {
                $msg = $_tmp['message'];
            }else{
                // Create the page
                echo '<h2>Comments: ('.$f2comment->get_commentCount($_POST['id'])['content'].')</h2>';
                foreach($_tmp['content'] as $comment) {
                    $tmp = $comment['created_on'];
                    if($f2user->get_loggedIn()) {
                        // Is mod?
                        if($f2user->get_powerlevel($f2user->user_id)['content'] > $config['mod_level']) {
                            echo '<div class="commentBox' . ($comment['hidden'] == TRUE ? ' commentHidden':'') .'">';
                            echo '<img class="profileSmallPics" src="'.$f2user->get_useravatar($f2user->get_userid($comment['username'])['content'])['content'].'"><span>';
                            echo '<a class="username-small" href="'.$config['start_url'].'user/profile/'.$comment['username'].'">' . $comment['username'] . '</a>';
                            echo '<section> posted ' . $f2backend->timePassed($tmp) . '</section>';
                            echo '<div class="commentText">'.nl2br(CommentBBHandler($comment['comment'])).'</div>';
                            echo '<div class="commentBoats"><section rel="review-upboat-count" cid="'.$comment['id'].'">'.$f2comment->get_upboatCount($comment['id'])['content'].'</section><a class="cursor-enabled'.($f2comment->get_hasUpboated($comment['id'], $f2user->get_userid()['content'])['status'] ? ' upboated':'').'" rel="review-upboat" cid="' . $comment['id'] . '"><i class="fa fa-thumbs-up"></i></a></div>';
                            echo '</span></div>';
                        }else{
                            echo '<div class="commentBox">';
                            echo '<img class="profileSmallPics" src="'.$f2user->get_useravatar($f2user->get_userid($comment['username'])['content'])['content'].'"><span>';
                            echo '<a class="username-small" href="'.$config['start_url'].'user/profile/'.$comment['username'].'">' . $comment['username'] . '</a>';
                            echo '<section> posted ' . $f2backend->timePassed($tmp) . '</section>';
                            echo '<div class="commentText">'.($comment['hidden'] == FALSE ? nl2br(CommentBBHandler($comment['comment'])):'This comment was deleted.').'</div>';
                            echo '<div class="commentBoats"><section rel="review-upboat-count" cid="'.$comment['id'].'">'.$f2comment->get_upboatCount($comment['id'])['content'].'</section><a class="cursor-enabled'.($f2comment->get_hasUpboated($comment['id'], $f2user->get_userid()['content'])['status'] ? ' upboated':'').'" rel="review-upboat" cid="' . $comment['id'] . '"><i class="fa fa-thumbs-up"></i></a></div>';
                            echo '</span></div>';
                        }
                    }else{
                        echo '<div class="commentBox">';
                        echo '<img class="profileSmallPics" src="'.$f2user->get_useravatar($f2user->get_userid($comment['username'])['content'])['content'].'"><span>';
                        echo '<a class="username-small" href="'.$config['start_url'].'user/profile/'.$comment['username'].'">' . $comment['username'] . '</a>';
                        echo '<section> posted ' . $f2backend->timePassed($tmp) . '</section>';
                        echo '<div class="commentText">'.($comment['hidden'] == FALSE ? nl2br(CommentBBHandler($comment['comment'])):'This comment was deleted.').'</div>';
                        echo '<div class="commentBoats"><section rel="review-upboat-count" cid="'.$comment['id'].'">'.$f2comment->get_upboatCount($comment['id'])['content'].'</section><a ><i class="fa fa-thumbs-up"></i></a></div>';
                        echo '</span></div>';
                    }
                }
                // Paginator
                if($pages != 0){
                    echo '<div class="paginator"><section>Page:</section>';
                    for($i = 1; $i <= $pages; $i++) {
                        echo '
                        <a href="#" rel="review-comment-page" class="'.($page==$i ? 'selected':'').'" page="'.$i.'" rid="'.$_POST['id'].'">'.$i.'</a>
                    ';
                    }
                    echo '<section></section></div>';
                }

            }
            break;}

        case 'upboat': {
            if(!$f2user->get_loggedIn() || $f2user->get_isBanned()['status'] || !$f2user->get_hasAccess(-1, 'regular')['content']) {
                echo 'fail';
                break;
            }
            if(!isset($_POST['cid'])) {
                echo 'fail';
                break;
            }
            $result = $f2comment->add_upboat( $_POST['cid'], $f2user->get_userid()["content"]);
            echo ($result['status'] ? 'success':'fail');
            break;}

        case 'get_upboats': {
            if(!isset($_POST['cid'])) {
                echo 'fail';
                break;
            }
            echo $f2comment->get_upboatCount($_POST['cid'])['content'];
            break;}

        case 'add_comment': { // $nid, $authorid, $title, $comment
            if(!isset($_POST['review_id'], $_POST['comment']) || !$f2user->get_loggedIn() || !$f2user->get_hasAccess(-1, 'regular')['content']) {
                echo 'Error submitting comment. Please try again.';
                break;
            }
            if(strlen($_POST['comment']) < 1) {
                echo 'Comment is too short.';
                break;
            }
            if($f2user->get_isBanned()['status']) {
                echo 'Your account has been suspended.';
                break;
            }
            $response = $f2comment->add_addComment($_POST['review_id'], $f2user->get_userid()['content'], $_POST['comment']);
            echo ($response['status'] ? 'success':"You can post a maximum of {$config['comments_per_minute']} post per minute.");
            break;}

        case 'review-add':
            if( !$f2user->get_loggedIn()) {
                echo '{ "status": "warning", "message": "You have to be logged in to do this." }';
                break;
            }
            if( empty($_POST['item']) || empty($_POST['title']) || empty($_POST['review']) || empty($_POST['rating']) ) {
                echo '{ "status": "error", "message": "No data was received." }';
                break;
            }
            if( !is_numeric($_POST['item']) || !$f2item->get_itemExists($_POST['item']) ) {
                echo '{ "status": "warning", "message": "Invalid item." }';
                break;
            }
            if( !is_numeric($_POST['rating']) || $_POST['rating'] > 10 || $_POST['rating'] < 1) {
                echo '{ "status": "warning", "message": "Invalid rating, please enter a value between 1 and 10." }';
                break;
            }
            if( strlen($_POST['title']) > 64 ) {
                echo '{ "status": "warning", "message": "Title cannot be longer than 64 characters." }';
                break;
            }
            if( $f2user->get_isBanned($f2user->user_id)['status']  || !$f2user->get_hasAccess($f2user->user_id, "regular")) {
                echo '{ "status": "warning", "message": "You do not have permission to do this action." }';
                break;
            }

            $obj = $f2review->add_review($f2user->user_id, $_POST['item'], $_POST['title'], $_POST['review'], $_POST['rating']);
            if($obj['status']) {
                echo '{ "status": "success", "message": "success", "url": "http://forty2reviews.com/review/'.$obj['content'].'" }'; // Do some shit
            }else{
                echo '{ "status": "error", "message": "AN unexpected error occurred"}';
            }

            break;

        default: {
        $msg = 'fatal_error_no_data_received';
        break;}
    }
}else{
    $msg = 'fatal_error_no_data_received';
}