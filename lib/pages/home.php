<?php
global $config;
$body .= $head_news;
$body .= '
<div class="row">
    <div class="col-4">
        <h2>Latest games</h2>
        ';
$return = $f2frontend->get_latestGames(10);
if($return['status']) {
    $items = $return['content'];

    foreach($items as $item) {
        $body .= '<a class="itemname-small" href="'.$config['start_url'].'item/'.$item['type_name'].'/' .urlencode($item['item_name']).'">'.$item['item_name'].'</a><br/>';
    }
}
$body .='
    </div>
    <div class="col-4">
        <h2>Latest animu</h2>
        Hier komen  de nieuwste animus
    </div>
    <div class="col-4">
        <h2>Updates</h2>
        Hier komen alle updates van de site
    </div>
</div>
<div class="row">
    <div class="col-12 list">
        <h2>Latest reviews</h2>
        ';
$return = $f2frontend->get_latestReviews(10);
if($return['status']) {
    $reviews = $return['content'];
    foreach($reviews as $review) {
        //f2reviews.id, f2reviews.title, f2reviews.review, f2users.username, f2item.item_name, f2reviews.created_on
        $body .= '<section><a class="username-large" href="'.$config['start_url'].'user/profile/' .$review['username'].'">'.$review['username'].'</a> <i class="fa fa-angle-double-right"></i> <a class="itemname-small" href="'.$config['start_url'].'review/' .$review['id'].'">'.$review['title'].' ('.$review['rating'].' / 10) </a> <i class="fa fa-angle-right"></i> <a class="itemname-small" href="'.$config['start_url'].'item/'.$review['type_name'].'/' .urlencode($review['item_name']).'"> '.$review['item_name'].' ('.$review['type_name'].')</a> <span>'.$f2backend->timePassed($review['created_on']).'</span></section>';

    }
}
$body .='
    </div>
</div>
<div class="row">
    <div class="col-4">
        <div class="message"><h3>'.$f2frontend->userCount()['users'].' users op onze site!</h3></div>
    </div>
    <div class="col-4">
        <div class="message"><h3>'.$f2frontend->reviewCount()['reviews'].' reviews op onze site!</h3></div>
    </div>
    <div class="col-4">
        <div class="message"><h3>'.$f2frontend->newsCount()['articles'].' articles op onze site!</h3></div>
    </div>
</div>
';