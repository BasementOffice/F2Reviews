<?php

/****************
f2review class
 * review system
****************/

// f2review -> get_review, add_review, rate_review, review_like, review_dislike

// f2reviews
// - id
// - author_id
// - item_id
// - created_on
// - title
// - review
// - hidden
// - likes
// - dislikes
// - rating


require_once($_SERVER['DOCUMENT_ROOT'] . 'lib/secret/config.cfg.php');

class f2review {

	Public function get_review($id) {
		global $pdo, $config;

        if(!$this->priv_reviewExists($id)) {
            return array("status" => FALSE, "message" => "invalid_review");
        }
		$query = $pdo->prepare('
			SELECT f2reviews.id, f2users.username, f2users.user_rank, f2items.item_name, f2items.item_description, f2types.type_name, f2reviews.title, f2reviews.review, f2reviews.hidden, f2reviews.likes, f2reviews.dislikes, f2reviews.rating, f2reviews.created_on
			FROM (f2reviews INNER JOIN f2users ON f2reviews.author_id = f2users.id INNER JOIN f2items ON f2reviews.item_id = f2items.id INNER JOIN f2types ON f2types.id = f2items.type_id)
			WHERE f2reviews.id = :rid');
		$query->bindValue(":rid", $id, PDO::PARAM_INT );
		$query->execute();

		$review = $query->fetch();

		$query = $pdo->prepare('SELECT item_id FROM f2reviews WHERE id = :rid');
		$query->bindValue(":rid", $id, PDO::PARAM_INT );
		$query->execute();
		$_tmp = $query->fetch();

		$query = $pdo->prepare('SELECT genre_name FROM (f2itemgenres INNER JOIN f2genres ON f2genres.id = f2itemgenres.genre_id) WHERE f2itemgenres.item_id = :iid');
		$query->bindValue(':iid', $_tmp[0], PDO::PARAM_INT);
		$query->execute();
		$genres = $query->fetchAll();

		$review['genres'] = $genres;

        return array("status" => TRUE, "message" => "success", "content" => $review);

	}

    public function get_reviewsFromUser($username = "") {
        global $pdo, $config, $f2user;

        if(!$f2user->get_userExists($username)) {
            return array("status" => FALSE, "message" => "invalid_user", "error" => $username . ' - ' . (string)$f2user->get_userExists($username));
        }

        $query = $pdo->prepare('
            SELECT f2reviews.id, f2reviews.title, f2reviews.review, f2users.username, f2items.item_name, f2reviews.created_on, f2types.type_name, f2reviews.rating
            FROM (f2reviews INNER JOIN f2users ON f2reviews.author_id = f2users.id INNER JOIN f2items ON f2items.id = f2reviews.item_id INNER JOIN f2types ON f2items.type_id = f2types.id)
            WHERE (f2reviews.hidden = 0 AND f2users.username = :username)
            ORDER BY f2reviews.id DESC
        ');
        $query->bindValue(":username", $username, PDO::PARAM_STR);
        $query->execute();

        return array("status" => TRUE, "message" => "success", "content" => $query->fetchAll());
    }

	public function get_reviewlist_type($type_id) {
		global $pdo, $config;

		$query = $pdo->prepare('
			SELECT f2reviews.id, f2reviews.title, f2reviews.rating, f2reviews.likes, f2reviews.dislikes, f2items.item_name
			FROM (f2reviews INNER JOIN f2items ON f2review.item_id = f2items.id)
			WHERE f2items.type_id = :tid
			ORDER BY f2reviews.created_on
			');
		$query->bindValue(':tid', $type_id, PDO::PARAM_INT);

		if ($query->execute()) {
            $reviews = $query->fetchAll();
            return array("status" => TRUE, "message" => "success", "content" => $reviews);
		} else {
			return array("status" => FALSE, "message" => "sql_error");
		}

	}

    public function get_reviewlist_item($item_id) {
		global $pdo, $config;

		$query = $pdo->prepare('
			SELECT f2reviews.id, f2reviews.title, f2reviews.rating, f2reviews.likes, f2reviews.dislikes
			FROM f2reviews
			WHERE f2reviews.item_id = :iid
			ORDER BY f2reviews.created_on
			');
		$query->bindvalue('iid', $item_id. PDO::PARAM_INT);

		if ($query->execute()) {
            $reviews = $query->fetchAll();
            return array("status" => TRUE, "message" => "success", "content" => $reviews);
		} else {
			return array("status" => FALSE, "message" => "sql_error");
		}

	}

    public function get_reviewlist_mostrecent() {
		global $pdo, $config;

		$query = $pdo->prepare('
			SELECT f2reviews.id, f2reviews.title, f2reviews.rating, f2reviews.likes, f2reviews.dislikes
			FROM f2reviews
			WHERE
			ORDER BY f2reviews.created_on
			');

	}

    public function get_reviewlist_genre($genre_id, $count = 5) {
		global $pdo, $config;

            if(!is_int($count)) {
                $amount = 5;
            }else{
                $amount = $count;
            }

		$query = $pdo->prepare('
			SELECT f2reviews.id, f2reviews.title, f2reviews.rating, f2reviews.likes, f2reviews.dislikes
			FROM (f2reviews INNER JOIN f2items ON f2reviews.id = f2items.id INNER JOIN f2itemgenres ON f2items.id = f2itemgenres.item_id)
			WHERE f2genres.genre_id = :gid
			ORDER BY f2reviews.created_on
			LIMIT ' . $amount . '
			');
		$query->bindValue('gid', $genre_id, PDO::PARAM_INT);

		if ($query->execute()) {
            $reviews = $query->fetchAll();
            return array("status" => TRUE, "message" => "success", "content" => $reviews);
		} else {
			return array("status" => FALSE, "message" => "sql_error");
		}
	}

    public function get_reviewCount($itemID = -1) {
        global $f2item, $pdo;
        if($itemID == -1 || !$f2item->get_itemExists($itemID)['content']) {
            // Get review count for ALL reviews\
            $query = $pdo->prepare("SELECT COUNT(*) FROM f2reviews");
            $query->execute();
            $count = $query->fetch()[0];

            return array("status" => TRUE, "message" => "success", "content" => $count);
        }

        $query = $pdo->prepare("SELECT COUNT(*) FROM f2reviews WHERE item_id = :iid");
        $query->bindValue(":iid", $itemID, PDO::PARAM_INT);
        $query->execute();
        $count = $query->fetch()[0];

        return array("status" => TRUE, "message" => "success", "content" => $count);
    }

    public function get_reviewList($itemID, $page=-1) {
        global $pdo, $f2item, $config;

        if(!$f2item->get_itemExists($itemID)['content']) {
            return array("status" => FALSE, "message" => "invalid_item", "content" => FALSE);
        }

        if(empty($page) || $page == -1 || (!is_numeric($page) || $page !== "last")) {
            // No page has been supplied, show all of the reviews!
            $query = $pdo->prepare("
            SELECT f2reviews.id, f2reviews.title, f2reviews.review, f2users.username, f2items.item_name, f2reviews.created_on, f2types.type_name, f2reviews.rating
            FROM (f2reviews INNER JOIN f2users ON f2reviews.author_id = f2users.id INNER JOIN f2items ON f2items.id = f2reviews.item_id INNER JOIN f2types ON f2items.type_id = f2types.id)
            WHERE f2reviews.hidden = 0 AND f2items.id = :iid
            ORDER BY f2reviews.created_on DESC");
            $query->bindValue(":iid", $itemID, PDO::PARAM_INT);
            $query->execute();

            return array("status" => TRUE, "message" => "success", "content" => $query->fetchAll() );
        }

        $query = $pdo->prepare('
            SELECT f2reviews.item_id, f2items.type_id, f2items.item_name, f2types.type_name
            FROM (f2reviews INNER JOIN f2items ON f2reviews.item_id = f2items.id INNER JOIN f2types ON f2types.id = f2items.type_id)
            WHERE f2items.id = :iid
            ORDER BY f2reviews.id ASC
			LIMIT ' . ($page-1) * $config['results_per_page'] . ', ' . $config['results_per_page']);
        $query->bindValue(":iid", $itemID, PDO::PARAM_INT);
        $query->execute();

        return array("status" => TRUE, "message" => "success", "content" => $query->fetchAll() );


    }

	public function add_review($author_id, $item_id, $title, $review, $rating) {
		global $pdo, $config;
		$tmp = new DateTime("now");

		$query = $pdo->prepare('
			INSERT INTO `f2reviews`
			(author_id, item_id, created_on,title, review, rating)
			VALUES
			(:aid, :iid, :con, :tit, :rev, :rat)
			');
		$query->bindValue(":aid", $author_id, PDO::PARAM_INT);
		$query->bindValue(":iid", $item_id, PDO::PARAM_INT);
		$query->bindValue(":con", $tmp->format("Y-m-d H:i:s"), PDO::PARAM_STR);
		$query->bindValue(":tit", $title, PDO::PARAM_STR);
		$query->bindValue(":rev", $review, PDO::PARAM_STR);
        $query->bindValue(":rat", $rating, PDO::PARAM_STR);


		if ($query->execute()) {
			return array("status" => TRUE, "message" => "succes", "content" => $pdo->lastInsertId());
		} else {
			return array("status" => FALSE, "message" => "sql_error");
		}
	}

    private function priv_reviewExists($id) {
        global $pdo, $config;

        $query = $pdo->prepare("SELECT count(*) FROM f2reviews WHERE id = :rid");
        $query->bindValue(":rid", $id, PDO::PARAM_INT);

        $query->execute();
        $_tmp = $query->fetch();

        return (boolean)$_tmp[0];
    }

}