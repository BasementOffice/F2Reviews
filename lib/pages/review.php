<?php
//TODO: Add a more reviews for this item
/****************
News page:
 * Comments
 * News text
 * Op info
 ****************/
$body = '';
if(is_numeric($_GET['page2'])) {
    $_tmp = $f2review->get_review($_GET['page2']);
    if($_tmp['status'] == FALSE || empty($_tmp['content'])) {
        $body = '
                <div class="row">
                    <div class="col-4">
                        <br>
                    </div>
                    <div class="col-4">
                        <div class="message warning"><h3>' . $_tmp['message'] . '</h3></div>
                    </div>
                    <div class="col-4">
                        <br>
                    </div>
                </div>';
        $crumbName = 'Invalid';
    }else{
        // Fixing stuff
        $_tmp['content']['title'] = nl2br(htmlentities($_tmp['content']['title']));
        $_tmp['content']['review'] = nl2br(ArticleBBHandler($_tmp['content']['review'])); // BB Parser
        $tmp = new DateTime($_tmp['content']['created_on']);
        $crumbName = ' - ' . $_tmp['content']['title'];
        $title = $_tmp['content']['title'];

        $body = '
            <div id="reviewid" rid="' . $_GET['page2'] . '" style="display:none;"></div>
            <div class="row">
                <div class="col-12">
                    <h2 class="itemname-large">'.$_tmp['content']['title'].'</h2>
                </div>
            </div>
            <div class="row reviewCols">
                <div class="col-8">
                    <p>'.$_tmp['content']['review'].'</p>
                </div>
                <div class="col-4">
                    <p>Posted by <a class="username-small" href="'.$config['start_url'].'user/profile/'.$_tmp['content']['username'].'#">'.$_tmp['content']['username'].'</a></p>
                    <section>'.$f2user->get_rankTitle($f2user->get_userid($_tmp['content']['username'])['content'])['content'].'</section>
                    <section>'.$_tmp['content']['user_rank'].'</section>
                    <img class="profilePics" src="'.$f2user->get_useravatar($f2user->get_userid($_tmp['content']['username'])['content'])['content'].'">
                    <section>'.$f2backend -> timePassed($tmp->format("d-m-Y H:i:s")).'</section>
                    <section>More reviews by <a class="username-small" href="'.$config['start_url'].'review/user/' .$_tmp['content']['username'].'">'.$_tmp['content']['username'].'</a></section>
                ';
        $__tmp = $f2item->get_item($f2item->get_nameToId($_tmp['content']['type_name'], $_tmp['content']['item_name'])['content']);
        if($__tmp['status']) {
            $item = $__tmp['content'];
            $body .= '
                <br><br><br><br><br><br><br><br><br><br><hr>
                    <a class="itemname-large" href="'.$config['start_url'].'item/'.$item['type_name'].'/' .urlencode($item['item_name']).'">'.$item['item_name'].'</a><br/>
                    <a href="'.$config['start_url'].'item/'.$item['type_name'].'/' .urlencode($item['item_name']).'"><img width="75%" height="" src="'.$config['start_url'].'uploads/images/'.$item['image_name'].'" /></a><br/>
            ';
            $body .= '<p>'.$item['item_short_description'].'</p>';
            $body .= '<section> Genres:<p> ';
                if(count($item['genres']) == 0) {
                    $body .= 'None yet.';
                }else{
                    $body .= implode(", ", $item['genres']);
                }
            $body .= '</p></section>';
        }else{
            $body .= '
                <hr>
                    <p>Item doesn\'t exist anymore.</p>
                ';
        }
        $body .= '</div>
            </div>';

        $body .= '</div></div><div class="body"><div class="container">';
        if($f2user->get_loggedIn() && !$f2user->get_isBanned()['status']) {
            $body .= '
            <div class="row">
                <div class="commentHolder col-12">
                    <form  class="" id="review-comment-add">
                        <img class="profileSmallPics" src="'.$f2user->get_useravatar($f2user->get_userid()['content'])['content'].'">
                        <input type="hidden" name="action" value="add_comment" />
                        <input type="hidden" name="review_id" value="'.$_tmp['content']['id'].'" />
                        <textarea class="" name="comment" placeholder="Tell others what you think"></textarea>
                        <a href="#" rel="review-comment-submit">Submit</a>
                    </form>
                </div>
            </div>
            ';
        }
        $body .='<div class="row"><div id="reviewComments" class="commentHolder col-12"></div></div>';
    }
}else switch ($_GET['page2']){
    case 'user': {
        if(!$f2user->get_userExists($_GET['page3'])) {
            $crumbName = ' - Invalid';
            $body = '
                <div class="row">
                    <div class="col-4">
                        <br>
                    </div>
                    <div class="col-4">
                        <div class="message warning"><h3>This user does not exist.</h3></div>
                    </div>
                    <div class="col-4">
                        <br>
                    </div>
                </div>';
            break;
        }
        // Get a list of reviews made by this user.
        $return = $f2review->get_reviewsFromUser($_GET['page3']);
        $body = '<div class="row">
                    <div class="col-10 list">
                        <h2 >Reviews by '.$_GET['page3'].'</h2>
                        ';
                if($return['status']) {
                    $reviews = $return['content'];

                    foreach($reviews as $review) {
                        //f2reviews.id, f2reviews.title, f2reviews.review, f2users.username, f2item.item_name, f2reviews.created_on
                        $body .= '<section><a></a><a href="'.$config['start_url'].'review/' .$review['id'].'">'.$review['title'].' ('.$review['rating'].' / 10) </a> <i class="fa fa-angle-right"></i> <a href="'.$config['start_url'].'item/'.$review['type_name'].'/' .urlencode($review['item_name']).'"> '.$review['item_name'].' ('.$review['type_name'].') </a> <span>'.$f2backend->timePassed($review['created_on']).'</span></section>';
                    }
                }
                if(count($return['content']) < 1) {
                    $body .= '<div class="message warning"><h3>This user does not have any reviews.</h3></div>';
                }

                $body .='
                    </div>
                    <div class="col-2 reviewCols">
                    <p><a href="'.$config['start_url'].'user/profile/' .$_GET['page3'].'">'.$_GET['page3'].'</a></p>
                    <section>'.$f2user->get_rankTitle($f2user->get_userid($_GET['page3'])['content'])['content'].'</section>
                    <img class="profilePics" src="'.$f2user->get_useravatar($f2user->get_userid($_GET['page3'])['content'])['content'].'">
                    </div>
                </div>';
        break;}

    case 'item':
        // Has an ID been provided?
        if(empty($_GET['page3'])) {
            $crumbName = ' - Invalid';
            $body = '
                <div class="row">
                    <div class="col-4">
                        <br>
                    </div>
                    <div class="col-4">
                        <div class="message error"><h3>No item ID has been provided. Please select an item <a href="http://forty2reviews.com/item/">here</a>.</h3></div>
                    </div>
                    <div class="col-4">
                        <br>
                    </div>
                </div>';
            break;
        }
        // Does the item actually exist?
        if(!$f2item->get_itemExists($_GET['page3'])['content']) {
            $crumbName = ' - Invalid';
            $body = '
                <div class="row">
                    <div class="col-4">
                        <br>
                    </div>
                    <div class="col-4">
                        <div class="message warning"><h3>This item ID is not associated with an item. Please select an item <a class="itemname-small" href="http://forty2reviews.com/item/">here</a>.</h3></div>
                    </div>
                    <div class="col-4">
                        <br>
                    </div>
                </div>';
            break;
        }

        // Does it actually have any reviews?
        if($f2review->get_reviewCount($_GET['page3'])['content'] < 1) {
            $crumbName = ' - No reviews';
            $crumbName2 = ' - '.$f2item->get_item($_GET['page3'])['content']['item_name'].' ('.$f2item->get_item($_GET['page3'])['content']['type_name'].')';
            $body = '
                <div class="row">
                    <div class="col-4">
                        <br>
                    </div>
                    <div class="col-4">
                        <div class="message warning"><h3>'.$f2item->get_item($_GET['page3'])['content']['item_name'].' does not have any reviews. Be the first to write a review <a class="itemname-small" itemID="'.$_GET['page3'].'" rel="add-review-form" href="#">here</a>!</h3></div>
                    </div>
                    <div class="col-4">
                        <br>
                    </div>
                </div>';
            break;
        }

        // Paginator
        if(!is_numeric($_GET['page4']) && $_GET['page4'] !== "last") {
            $_GET['page4'] = 0;
        }
        // Get page count
        $reviewCount = $f2review->get_reviewCount($_GET['page3'])['content'];
        $pages = ceil($reviewCount / $config['results_per_page']);
        if($_GET['page4'] == "last") {
            $page = $pages;
        }else{
            $page = $_GET['page4'];
        }
        //--------------
        $crumbName2 = ' - '.$f2item->get_item($_GET['page3'])['content']['item_name'].' ('.$f2item->get_item($_GET['page3'])['content']['type_name'].')';
        $body .= '<div class="row"><div class="col-12 list">';
        $_tmp = $f2review->get_reviewList($_GET['page3'], $page);
        if($_tmp['status'] == FALSE) {
            $msg = $_tmp['message'];
        }else{
            $msg = "success";
            // Create the page
            foreach($_tmp['content'] as $review) {
                $body .= '<section><a class="username-large" href="'.$config['start_url'].'user/profile/' .$review['username'].'">'.$review['username'].'</a> <i class="fa fa-angle-double-right"></i> <a class="itemname-small" href="'.$config['start_url'].'review/' .$review['id'].'">'.$review['title'].' ('.$review['rating'].' / 10) </a> <i class="fa fa-angle-right"></i> <a class="itemname-small" href="'.$config['start_url'].'item/'.$review['type_name'].'/' .urlencode($review['item_name']).'"> '.$review['item_name'].' ('.$review['type_name'].')</a> <span>'.$f2backend->timePassed($review['created_on']).'</span></section>';
            }
            // Paginator
            if($pages != 0){
                $body .= '<div class="paginator"><section>Page:</section>';
                for($i = 1; $i <= $pages; $i++) {
                    $body .= '
                        <a href="'.$startUrl.$_GET['page1'].'/'.$_GET['page2'].'/'.urlencode($_GET['page3']).'/'.$i.'" class="'.($page==$i ? 'selected':'').'">'.$i.'</a>
                    ';
                }
                $body .= '<section></section></div>';
            }
            $body .= '</div></div>';

        }

        break;

    default:
        // Show a list of the most recent reviews
        $body = '<div class="row">
                    <div class="col-12 list">
                        <h2>Latest reviews</h2>
                    ';
                $return = $f2frontend->get_latestReviews(30);
                if($return['status']) {
                    $reviews = $return['content'];
                    foreach($reviews as $review) {
                        //f2reviews.id, f2reviews.title, f2reviews.review, f2users.username, f2item.item_name, f2reviews.created_on
                        $body .= '<section><a class="username-large" href="'.$config['start_url'].'user/profile/' .$review['username'].'">'.$review['username'].'</a> <i class="fa fa-angle-double-right"></i> <a class="itemname-small" href="'.$config['start_url'].'review/' .$review['id'].'">'.$review['title'].' ('.$review['rating'].' / 10) </a> <i class="fa fa-angle-right"></i> <a class="itemname-small" href="'.$config['start_url'].'item/'.$review['type_name'].'/' .urlencode($review['item_name']).'"> '.$review['item_name'].' ('.$review['type_name'].') </a> <span>'.$f2backend->timePassed($review['created_on']).'</span></section>';

                    }
                }
                $body .='
                    </div>
                </div>';
        break;
}
$body .= '';