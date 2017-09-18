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

// Check if an action has been set
if(!empty($_POST) && isset($_POST['action'])) {
    switch($_POST['action']) {
        case 'itemlist': {
            // Check if all data has been sent
            if(!isset($_POST['type']) || !$f2item->get_typeNameExists(@$_POST['type'])['content']) {$msg = 'corrupt_data';}
            if(!isset($_POST['page'])) {$_POST['page'] = 0;}
            if(!is_numeric($_POST['page']) && $_POST['page'] !== "last") {
                $_POST['page'] = 0;
            }
            // Get page count
            $commentCount = $f2item->get_itemCount($_POST['type'])['content'];
            $pages = ceil($commentCount / $config['results_per_page']);
            if($_POST['page'] == "last") {
                $page = $pages;
            }else{
                $page = $_POST['page'];
            }
            //--------------

            $_tmp = $f2item->get_itemList($_POST['type'], $page);
            if($_tmp['status'] == FALSE) {
                $msg = $_tmp['message'];
            }else{
                $msg = "success";
                // Create the page
                echo '<h2>'.$_POST['type'].'s: ('.$f2item->get_itemCount($_POST['type'])['content'].')</h2>';
                foreach($_tmp['content'] as $item) {
                    echo '<section><a class="itemname-large" href="'.$config['start_url'].'item/'.$item['type_name'].'/' .urlencode($item['item_name']).'">'.$item['item_name'].'</a></section>';
                }
                // Paginator
                if($pages != 0){
                    echo '<div class="paginator"><section>Page:</section>';
                    for($i = 1; $i <= $pages; $i++) {
                        echo '
                        <a href="#" rel="item-list-page" class="'.($page==$i ? 'selected':'').'" page="'.$i.'" type="'.$_POST['type'].'">'.$i.'</a>
                    ';
                    }
                    echo '<section></section></div>';
                }

            }
        break;}
    }
}else{
    $msg = 'fatal_error_no_data_received';
}

