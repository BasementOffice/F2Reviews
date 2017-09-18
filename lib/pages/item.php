<?php
/**
 * Created by PhpStorm.
 * User: Root
 * Date: 4/4/15
 * Time: 2:02 PM
 */
if(empty($_GET['page2'])){
    $body = '
        <div class="row">
            <div class="col-3">
                <br>
            </div>
            <div class="col-6 list">
                <div class="message warning"><h3>You haven\'t selected an item type, please select one!</h3></div>
                ';
            $_tmp = $f2frontend->get_typeList();
            if($_tmp['status'] && count($_tmp['content']) > 0) {
                foreach($_tmp['content'] as $listItem) {
                    $body .= '<section><a class="itemname-large" href="'.$config['start_url'].'item/'.$listItem['type_name'].'">'.$listItem['type_name'].'s</a></section>';
                }
            }else{
                $body .= '<div class="message error"><h3>Unable to retrieve list.</h3></div>';
            }
            $body .= '</div>
            <div class="col-3">
                <br>
            </div>
        </div>';
    $crumbName = ' - Invalid';
}elseif(!$f2item->get_typeNameExists($_GET['page2'])['content'] || !$f2item->get_typeNameExists($_GET['page2'])['status']) {
    // Show error
    $body = '
        <div class="row">
            <div class="col-4">
                <br>
            </div>
            <div class="col-4">
                <div class="message warning"><h3>You\'re trying to acces an invalid item type.</h3></div>
            </div>
            <div class="col-4">
                <br>
            </div>
        </div>';
    $crumbName = ' - Invalid';
}else{
    switch($_GET['page3']) {
        case '': { // If none was provided
            $body = '
                <div class="row">
                    <div class="col-2">
                        <br>
                    </div>
                    <div class="col-8 list">
                        <div rel="items-list" type="'.$_GET['page2'].'"></div>
                    </div>
                    <div class="col-2">
                        <br>
                    </div>
                </div>';
        break;}

        default: {
            // Does this item exist?
            if(!$f2item->get_itemNameExists($_GET['page2'], $_GET['page3'])['content'] || !$f2item->get_itemNameExists($_GET['page2'], $_GET['page3'])['status']) {
                // Show error
                $body = '
            <div class="row">
                <div class="col-3">
                    <br>
                </div>
                <div class="col-6">
                    <div class="message warning"><h3>We do not have a(n) '.$_GET['page2'].' with this name in our database, sorry!</h3></div>
                </div>
                <div class="col-3">
                    <br>
                </div>
            </div>';
                $crumbName = ' - Invalid';
            break;}
            $_tmp = $f2item->get_item($f2item->get_nameToId($_GET['page2'], $_GET['page3'])['content']);
            if($_tmp['status'] == FALSE || count($_tmp['content']) == 0) {
                $body = '
                    <div class="row">
                        <div class="col-3">
                            <br>
                        </div>
                        <div class="col-6">
                            <div class="message warning"><h3>An error occured while retrieving data.</h3></div>
                        </div>
                        <div class="col-3">
                            <br>
                        </div>
                    </div>';
            break;}
            $item = $_tmp['content'];
            $body = '
                <div class="row">
                    <div class="col-4">
                        <img width="96%" style="margin-top: 20px" src="'.$config['start_url'].'uploads/images/'.$item['image_name'].'" /><br/>
                        <section> Genres:
                            <b>';
            if(count($item['genres']) == 0) {
                $body .= 'None yet.';
            }else{
                $body .= implode(", ", $item['genres']);
            }
            $body .= '      </b>

                        </section>

                        <section>Reviews: <a class="itemname-small" href="http://forty2reviews.com/review/item/'.$f2item->get_nameToId($_GET['page2'], $_GET['page3'])['content'].'">'.$f2review->get_reviewCount($f2item->get_nameToId($_GET['page2'], $_GET['page3'])['content'])['content'].'</a></section>
                        <section>Average rating: <b>'.round($f2item->get_averageRating($f2item->get_nameToId($_GET['page2'], $_GET['page3'])['content'])['content'], 1).'</b></section>
                        <section>Type: <b>'.$_GET['page2'].'</b></section>
                        <section>Add a review <a class="itemname-small" itemID="'.$f2item->get_nameToId($_GET['page2'], $_GET['page3'])['content'].'" rel="add-review-form" href="#">here</a></section>
                    </div>
                     <div class="col-8">
                        <h2 class="itemname-large">'.$item['item_name'].'</h2>
                        <p>'.nl2br(ArticleBBHandler($item['item_description'])).'</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-8"><br></div>
                    <div class="col-4"><br></div>
                </div>
            ';
        break;}
    }
}