<?php
/**
 * Created by PhpStorm.
 * User: Mitchel
 * Date: 3/19/15
 * Time: 11:13 AM
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "lib/secret/config.cfg.php");

class f2news {

    public function get_latestNews($count = 5) {
        global $pdo, $config;

        if(!is_numeric($count)) {
            $amount = 5;
        }else{
            $amount = $count;
        }

        // Get the news from the database
        $query = $pdo->prepare("
        SELECT f2news.id, f2news.created_on, f2news.title, f2news.news_pic, f2news.news, f2users.username
        FROM (f2news INNER JOIN f2users ON f2news.author_id = f2users.id)
        ORDER BY f2news.id DESC LIMIT " . $amount );

        if($query->execute()) {
            $rtn = array("status" => TRUE, "message" => "success");
        }else{
            return array("status" => FALSE, "message" => "sql_error");
        }

        $rtn["content"] = $query->fetchAll();

        // Return
        return $rtn;
    }

    public function get_news($id) {
        global $pdo, $config;
        if($this->priv_existsNews($id)) {
            // Get news article from database
            $query = $pdo->prepare("
            SELECT f2news.id, f2news.created_on, f2news.title, f2news.news, f2users.username, f2users.user_rank
            FROM (f2news INNER JOIN f2users ON f2news.author_id = f2users.id)
            WHERE f2news.id = :nid
            ");
            $query->bindValue(":nid", $id, PDO::PARAM_INT);

            if(!$query->execute()) {
                return array("status" => FALSE, "message" => "sql_error");
            }

            return array("status" => TRUE, "message" => "success", "content" => $query->fetch());

        }else{
            return array("status" => FALSE, "message" => "News article not found.");
        }
    }

    public function add_insertNews($title, $news, $author) {
        global $pdo, $config;
        // Create variables
        $now = new DateTime("now");

        // Do some checks
        if($author == NULL || $title == NULL || $news == NULL ||
           $author == ""   || $title == ""   || $news == "") {
            return array("status" => FALSE, "message" => "no_data_supplied");
        }
        if(strlen($title) > 64) {
            return array("status" => FALSE, "message" => "title_exceeds_limit");
        }
        $author_id      = $author;
        $news_title     = $title;
        $news_msg       = $news;
        $con            = $now->format("Y-m-d H:i:s");

        // Insert the news
        $query = $pdo->prepare("
        INSERT INTO `f2news`
        (`author_id`, `created_on`, `title`, `news`) VALUES
        (:aid, :con, :title, :news)");
        $query->bindValue(":aid", $author_id, PDO::PARAM_INT);
        $query->bindValue(":con", $con, PDO::PARAM_STR);
        $query->bindValue(":title", $news_title, PDO::PARAM_STR);
        $query->bindValue(":news", $news_msg, PDO::PARAM_STR);

        if($query->execute()) {
            return array("status" => TRUE, "message" => "success");
        }else{
            return array("status" => FALSE, "message" => "sql_error");
        }
    }

    public function mod_editNews($id, $title, $news) {
        global $pdo, $config;
        // Checks
        if($id == NULL || $title == NULL || $news == NULL ||
           $id == ""   || $title == ""   || $news == "") {
            return array("status" => FALSE, "message" => "no_data_supplied");
        }
        if(strlen($title) > 64) {
            return array("status" => FALSE, "message" => "title_exceeds_limit");
        }

        $v_id       = $id;
        $v_title    = $title;
        $v_news     = $news;

        // Update the news
        $query = $pdo->prepare("UPDATE f2news SET title = :title, news = :news WHERE id = :id");
        $query->bindValue(":title", $v_title, PDO::PARAM_STR);
        $query->bindValue(":news", $v_news, PDO::PARAM_STR);
        $query->bindValue(":id", $v_id, PDO::PARAM_INT);

        if($query->execute()) {
            return array("status" => TRUE, "message" => "success");
        }else{
            return array("status" => FALSE, "message" => "sql_error");
        }
    }

    public function rm_removeNews($id = 0) {
        global $pdo, $config;
        // Checks
        if($id == NULL || $id == 0) {
            return array("status" => FALSE, "message" => "no_data_supplied");
        }

        // Remove the news
        $query = $pdo->prepare("DELETE FROM f2news WHERE id = :id LIMIT 1");
        $query->bindValue(":id", $id, PDO::PARAM_INT);

        if($query->execute()) {
            return array("status" => TRUE, "message" => "success");
        }else{
            return array("status" => FALSE, "message" => "sql_error");
        }
    }

    private function priv_existsNews($id) {
        global $pdo, $config;

        $query = $pdo->prepare("SELECT count(*) FROM `f2news` WHERE `id` = :nid");
        $query->bindValue(":nid", $id, PDO::PARAM_INT);

        if(!$query->execute()) {
            return FALSE;
        }
        $result = $query->fetch();

        return (boolean)$result[0];
    }
}