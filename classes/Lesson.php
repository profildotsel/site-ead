<?php
class Lesson {
    private $conn;
    private $table_name = "lessons";

    public $id;
    public $course_id;
    public $title;
    public $content;
    public $order;
    public $video_url;
    public $order_number;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET course_id=:course_id, title=:title, content=:content, `order`=:order, video_url=:video_url";

        $stmt = $this->conn->prepare($query);

        $this->course_id = htmlspecialchars(strip_tags($this->course_id));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = $this->content; // Permitir HTML nas aulas
        $this->order = $this->order_number ? intval($this->order_number) : 1;
        $this->video_url = $this->video_url ? htmlspecialchars(strip_tags($this->video_url)) : null;

        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":order", $this->order);
        $stmt->bindParam(":video_url", $this->video_url);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readByCourse() {
        $query = "SELECT id, title, content, `order`, video_url, created_at
                 FROM " . $this->table_name . "
                 WHERE course_id = ?
                 ORDER BY `order` ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->course_id);
        $stmt->execute();

        return $stmt;
    }

    public function readOne() {
        $query = "SELECT course_id, title, content, `order`, video_url, created_at
                 FROM " . $this->table_name . "
                 WHERE id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->course_id = $row['course_id'];
            $this->title = $row['title'];
            $this->content = $row['content'];
            $this->order = $row['order'];
            $this->video_url = $row['video_url'];
            $this->created_at = $row['created_at'];
        }
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET title = :title, content = :content, `order` = :order, video_url = :video_url
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = $this->content; // Permitir HTML
        $this->order = $this->order_number ? intval($this->order_number) : 1;
        $this->video_url = $this->video_url ? htmlspecialchars(strip_tags($this->video_url)) : null;
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':order', $this->order);
        $stmt->bindParam(':video_url', $this->video_url);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getNextOrder($course_id) {
        $query = "SELECT MAX(`order`) as max_order FROM " . $this->table_name . " WHERE course_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $course_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['max_order'] ?? 0) + 1;
    }
}
?>