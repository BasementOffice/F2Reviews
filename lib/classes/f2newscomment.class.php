<?php

/****************
f2comment class
 * login system
****************/
require_once($_SERVER['DOCUMENT_ROOT'] . "lib/secret/config.cfg.php");


class f2newscomment {

    public function get_newsComments($id, $page = 0) {
        global $pdo, $config;

        if(!$this->priv_existsNews($id)) {
            return array("status" => FALSE, "message" => "invalid_news");
        }
        $query = $pdo->prepare('
			SELECT f2users.username, f2newscomments.id, f2newscomments.comment, f2newscomments.created_on, f2newscomments.hidden, f2newscomments.upboats
			FROM (f2newscomments INNER JOIN f2users ON f2newscomments.author_id = f2users.id)
			WHERE f2newscomments.news_id = :nid
			ORDER BY f2newscomments.id ASC
			LIMIT ' . ($page-1) * $config['results_per_page'] . ', ' . $config['results_per_page']);
        $query->bindValue(":nid", $id, PDO::PARAM_INT );
        $query->execute();

        $comments = $query->fetchAll();

        return array("status" => TRUE, "message" => "success", "content" => $comments);
    }

    public function rm_deleteComment($id) {
        global $pdo, $config;

        if(!$this->priv_existsComment($id)) {
            return array("status" => FALSE, "message" => "invalid_id");
        }

        $query = $pdo->prepare("UPDATE f2newscomments SET hidden = 1 WHERE id = :ncid");
        $query->bindValue(":ncid", $id, PDO::PARAM_INT);
        $query->execute();

        return array("status" => TRUE, "message" => "success");
    }

    public function mod_editComment($id, $comment) {
        global $pdo, $config;

        if(!$this->priv_existsComment($id)) {
            return array("status" => FALSE, "message" => "invalid_id");
        }

        $query = $pdo->prepare("UPDATE f2newscomments SET comment = :comment WHERE id = :ncid");
        $query->bindValue(":comment", $comment, PDO::PARAM_STR);
        $query->bindValue(":ncid", $id, PDO::PARAM_INT);
        $query->execute();

        return array("status" => TRUE, "message" => "success");
    }

    public function add_addComment($nid, $authorid, $comment) {
        global $pdo, $config;

        if(!$this->priv_existsNews($nid)) {
            return array("status" => FALSE, "message" => "invalid_news");
        }

        if(!$this->priv_canComment($authorid)) {
            return array("status" => FALSE, "message" => "post_time_limit");
        }

        $tmp = new DateTime("now");
        $query = $pdo->prepare("
        INSERT INTO f2newscomments
        (news_id, author_id, comment, created_on)
        VALUES
        (:nid, :aid, :comment, :con)
        ");

        $query->bindValue(":nid", $nid, PDO::PARAM_INT);
        $query->bindValue(":aid", $authorid, PDO::PARAM_INT);
        $query->bindValue(":comment", $comment, PDO::PARAM_STR);
        $query->bindValue(":con", $tmp->format("Y-m-d H:i:s"), PDO::PARAM_STR);

        $query->execute();

        return array("status" => TRUE, "message" => "success");
    }

    public function get_hasUpboated($cid, $aid) {
        global $pdo, $config;

        if(!$this->priv_existsComment($cid)) {
            return array("status" => FALSE, "message" => "invalid_id");
        }

        $query = $pdo->prepare("SELECT COUNT(*) FROM f2newsupboats WHERE newscomment_id = :cid AND user_id = :aid");
        $query->bindValue(":cid", $cid, PDO::PARAM_INT);
        $query->bindValue(":aid", $aid, PDO::PARAM_INT);
        $query->execute();

        return array("status" => (boolean)$query->fetch()[0], "message" => "success");
    }

    public function add_upboat($cid, $aid) {
        global $pdo, $config;

        if($this->get_hasUpboated($cid, $aid)['status']) {
            $query = $pdo->prepare("
            DELETE FROM f2newsupboats WHERE newscomment_id = :cid AND user_id = :aid LIMIT 1
            ");
            $query->bindValue(":cid", $cid, PDO::PARAM_INT);
            $query->bindValue(":aid", $aid, PDO::PARAM_INT);
            $query->execute();
            return array("status" => FALSE, "message" => "removed_upboat");
        }else{
            $query = $pdo->prepare("
            INSERT INTO f2newsupboats
            (newscomment_id, user_id)
            VALUES
            (:cid, :aid)
            ");
            $query->bindValue(":cid", $cid, PDO::PARAM_INT);
            $query->bindValue(":aid", $aid, PDO::PARAM_INT);
            $query->execute();

            return array("status" => TRUE, "message" => "success");
        }
    }

    public function get_commentCount($nid) {
        global $pdo, $config;

        if(!$this->priv_existsNews($nid)) {
            return array("status" => FALSE, "message" => "invalid_news");
        }

        $query = $pdo->prepare("SELECT COUNT(*) FROM f2newscomments WHERE news_id = :nid");
        $query->bindValue(":nid", $nid, PDO::PARAM_INT);
        $query->execute();

        return array("status" => TRUE, "message" => "success", "content" => $query->fetch()[0]);
    }

    public function get_upboatCount($cid) {
        global $pdo, $config;

        if(!$this->priv_existsComment($cid)) {
            return array("status" => FALSE, "message" => "invalid_news", "content" => 0);
        }

        $query = $pdo->prepare("SELECT COUNT(*) FROM f2newsupboats WHERE newscomment_id = :cid");
        $query->bindValue(":cid", $cid, PDO::PARAM_INT);
        $query->execute();

        return array("status" => TRUE, "message" => "success", "content" => $query->fetch()[0]);
    }

    //-----------------------------------------------------------------------------

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

    private function priv_existsComment($ncid) {
        global $pdo, $config;

        $query = $pdo->prepare("SELECT count(*) FROM `f2newscomments` WHERE `id` = :ncid");
        $query->bindValue(":ncid", $ncid, PDO::PARAM_INT);
        $query->execute();

        $result = $query->fetch();

        return (boolean)$result[0];
    }

    private function priv_canComment($id) {
        global $pdo, $config;

        $query = $pdo->prepare("SELECT count(*) FROM f2newscomments WHERE (author_id = :id AND created_on > date_sub(now(), interval 1 minute))");
        $query->bindValue(":id", $id, PDO::PARAM_INT);
        $query->execute();

        $result = $query->fetch();

        return ($result[0] < $config['comments_per_minute']);
    }

}