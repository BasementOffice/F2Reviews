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

class f2item {

    Public function get_item($id) {
        global $pdo, $config;

        if(!$this->get_itemExists($id)['content']) {
            return array("status" => FALSE, "message" => "invalid_review");
        }
        $query = $pdo->prepare('
			SELECT f2items.id, f2items.item_short_description, f2items.item_description, f2items.item_name, f2types.type_name, f2images.image_name
			FROM (f2items INNER JOIN f2types ON f2items.type_id = f2types.id INNER JOIN f2images ON f2images.id = f2items.image)
			WHERE f2items.id = :iid');
        $query->bindValue(":iid", $id, PDO::PARAM_INT );
        $query->execute();

        $item = $query->fetch();

        $query = $pdo->prepare('SELECT genre_name FROM (f2itemgenres INNER JOIN f2genres ON f2genres.id = f2itemgenres.genre_id) WHERE f2itemgenres.item_id = :iid');
        $query->bindValue(':iid', $id, PDO::PARAM_INT);
        $query->execute();
        $genres = $query->fetchAll();
        foreach($genres as $genre) {
            $item['genres'][] = $genre['genre_name'];
        }
        return array("status" => TRUE, "message" => "success", "content" => $item);
    }

    Public function get_averageRating($id) {
        global $pdo, $config;

        if(!$this->get_itemExists($id)['content']) {
            return array("status" => FALSE, "message" => "invalid_review", "content" => 0);
        }
        $query = $pdo->prepare('
			SELECT AVG(f2reviews.rating)
			FROM (f2reviews INNER JOIN f2items ON f2reviews.item_id = f2items.id)
			WHERE f2items.id = :iid');
        $query->bindValue(":iid", $id, PDO::PARAM_INT );
        $query->execute();
        $rating = $query->fetch()[0];
        return array("status" => TRUE, "message" => "success", "content" => $rating);
    }

    public function get_nameToId($type, $name) {
        global $pdo, $config;

        $query = $pdo->prepare("
        SELECT f2items.id
        FROM (f2items INNER JOIN f2types ON f2items.type_id = f2types.id)
        WHERE type_name = :type AND item_name = :name"
        );
        $query->bindValue(":name", $name, PDO::PARAM_STR);
        $query->bindValue(":type", $type, PDO::PARAM_STR);
        $query->execute();

        return array("status" => TRUE, "message" => "success", "content" => $query->fetch()[0]);
    }

    public function get_itemCount($type) {
        global $pdo, $config;

        $query = $pdo->prepare("SELECT COUNT(*) FROM (f2items INNER JOIN f2types ON f2items.type_id = f2types.id) WHERE f2types.type_name = :type");
        $query->bindValue(":type", $type, PDO::PARAM_STR);
        $query->execute();

        return array("status" => TRUE, "message" => "success", "content" => $query->fetch()[0]);
    }

    public function get_itemList($type, $page = 0) {
        global $pdo, $config;

        if(!$this->get_typeNameExists($type)['content']) {
            return array("status" => FALSE, "message" => "invalid_type");
        }
        $query = $pdo->prepare('
			SELECT f2items.item_name, f2types.type_name
			FROM (f2items INNER JOIN f2types ON f2items.type_id = f2types.id)
			WHERE f2types.type_name = :type
			ORDER BY f2items.item_name ASC
			LIMIT ' . ($page-1) * $config['results_per_page'] . ', ' . $config['results_per_page']);
        $query->bindValue(":type", $type, PDO::PARAM_INT );
        $query->execute();

        $items = $query->fetchAll();

        return array("status" => TRUE, "message" => "success", "content" => $items);
    }

    public function get_typeNameExists($name) {
        global $pdo, $config;

        $query = $pdo->prepare("SELECT COUNT(*) FROM f2types WHERE type_name = :name");
        $query->bindValue(":name", $name, PDO::PARAM_STR);
        $query->execute();

        return array("status" => TRUE, "message" => "success", "content" => (boolean)$query->fetch()[0]);
    }

    public function get_itemNameExists($type, $name) {
        global $pdo, $config;

        $query = $pdo->prepare("
        SELECT COUNT(*)
        FROM (f2items INNER JOIN f2types ON f2items.type_id = f2types.id)
        WHERE type_name = :type AND item_name = :name"
        );
        $query->bindValue(":name", $name, PDO::PARAM_STR);
        $query->bindValue(":type", $type, PDO::PARAM_STR);
        $query->execute();

        return array("status" => TRUE, "message" => "success", "content" => (boolean)$query->fetch()[0]);
    }

    public function get_itemExists($id) {
        global $pdo, $config;

        $query = $pdo->prepare("
        SELECT COUNT(*)
        FROM f2items
        WHERE f2items.id = :id"
        );
        $query->bindValue(":id", $id, PDO::PARAM_INT);
        $query->execute();

        return array("status" => TRUE, "message" => "success", "content" => (boolean)$query->fetch()[0]);
    }

}