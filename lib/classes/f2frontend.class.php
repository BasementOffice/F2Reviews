<?php

/****************
f2frontend class:
 * for retrieving
 * database stats

Class name:
 * f2frontend

Functions:
 * userCount
 * reviewCount
 * commentCount
 * newsCount
****************/

require_once($_SERVER['DOCUMENT_ROOT'] . 'lib/secret/config.cfg.php');

class f2frontend {
    function userCount(){
        global $pdo, $config;

        $temp = array(
            "users"  => $pdo->prepare("SELECT COUNT(*) FROM f2users"),
            "activated"  => $pdo->prepare("SELECT COUNT(*) FROM f2users WHERE activated = 1"),
            "unactivated"  => $pdo->prepare("SELECT COUNT(*) FROM f2users WHERE activated = 0"),
            'something' => $pdo->prepare(" ")
        );
        foreach($temp as $key =>  $t){
            $t->execute();
            $temp[$key] = $t->fetch();
            $temp[$key] = $temp[$key][0];
        }
        return $temp;
    }
    function reviewCount(){
        global $pdo, $config;
        $temp = array(
            "reviews"  => $pdo->prepare("SELECT COUNT(*) FROM f2reviews"),
        );
        foreach($temp as $key =>  $t){
            $t->execute();
            $temp[$key] = $t->fetch();
            $temp[$key] = $temp[$key][0];
        }
        return $temp;
    }
    function commentCount(){
        global $pdo, $config;

        $temp = array(
            "comments"  => $pdo->prepare("SELECT COUNT(*) FROM f2comments"),
        );
        foreach($temp as $key =>  $t){
            $t->execute();
            $temp[$key] = $t->fetch();
            $temp[$key] = $temp[$key][0];
        }
        return $temp;
    }
    function newsCount(){
        global $pdo, $config;
        $temp = array(
            "articles"  => $pdo->prepare("SELECT COUNT(*) FROM f2news"),
        );
        foreach($temp as $key =>  $t){
            $t->execute();
            $temp[$key] = $t->fetch();
            $temp[$key] = $temp[$key][0];
        }
        return $temp;
    }
    // Set up a pageview
    public function setView(){
        global $pdo, $config, $f2user;
        $tmp = new DateTime("now");
        $query = $pdo->prepare('
	 		INSERT INTO
	 		`f2visit`
	 		(user_id, ip, visit_datetime, page1, page2, page3, page4)
	 		VALUES
	 		(:user_id, :ip, :visit_datetime, :page1, :page2, :page3, :page4)
	 		');
        $query->bindValue(':user_id', (!empty($f2user->get_userid()['content'])? $f2user->get_userid()['content']:''), PDO::PARAM_STR);
        $query->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
        $query->bindValue(':visit_datetime', $tmp->format("Y-m-d H:i:s"), PDO::PARAM_STR);
        $query->bindValue(':page1', (!empty($_GET['page1'])? $_GET['page1']:''), PDO::PARAM_STR);
        $query->bindValue(':page2', (!empty($_GET['page2'])? $_GET['page2']:''), PDO::PARAM_STR);
        $query->bindValue(':page3', (!empty($_GET['page3'])? $_GET['page3']:''), PDO::PARAM_STR);
        $query->bindValue(':page4', (!empty($_GET['page4'])? $_GET['page4']:''), PDO::PARAM_STR);

        if(!$query->execute()) {
            return array("status" => FALSE, "message" => "sql_error");
        }
    }
    public function get_latestReviews($count = 5) {
        global $pdo, $config;

        if(!is_numeric($count)) {
            $amount = 5;
        }else{
            $amount = $count;
        }

        $query = $pdo->prepare("
        SELECT f2reviews.id, f2reviews.title, f2reviews.review, f2users.username, f2items.item_name, f2reviews.created_on, f2types.type_name, f2reviews.rating
        FROM (f2reviews INNER JOIN f2users ON f2reviews.author_id = f2users.id INNER JOIN f2items ON f2items.id = f2reviews.item_id INNER JOIN f2types ON f2items.type_id = f2types.id)
        WHERE f2reviews.hidden = 0
        ORDER BY f2reviews.id DESC LIMIT " . $amount );

        if($query->execute()) {
            $rtn = array("status" => TRUE, "message" => "success");
        }else{
            return array("status" => FALSE, "message" => "sql_error");
        }

        $rtn["content"] = $query->fetchAll();

        // Return
        return $rtn;
    }

    public function get_latestGames($count = 5) {
        global $pdo, $config;

        if(!is_numeric($count)) {
            $amount = 5;
        }else{
            $amount = $count;
        }

        $query = $pdo->prepare('
            SELECT f2items.id, f2items.item_name, f2types.type_name
            FROM (f2items INNER JOIN f2types ON f2items.type_id = f2types.id)
            WHERE f2types.type_name = "Game"
            ORDER BY f2items.id DESC LIMIT '.$amount);

        if($query->execute()) {
            $rtn = array("status" => TRUE, "message" => "success");
        }else{
            return array("status" => FALSE, "message" => "sql_error");
        }

        $rtn["content"] = $query->fetchAll();

        // Return
        return $rtn;

    }

    public function get_typeList() {
        global $pdo, $config;

        $query = $pdo->prepare("SELECT f2types.type_name FROM f2types");

        return array("status" => $query->execute(), "message" => "executed", "content" => $query->fetchAll());
    }
}