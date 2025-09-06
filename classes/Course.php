<?php
class Course {
    private $conn;
    private $table_name = "courses";

    public $id;
    public $title;
    public $description;
    public $instructor_id;
    public $status;
    public $start_date;
    public $end_date;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET title=:title, description=:description, instructor_id=:instructor_id,
                    status=:status, start_date=:start_date, end_date=:end_date";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->instructor_id = htmlspecialchars(strip_tags($this->instructor_id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->end_date = htmlspecialchars(strip_tags($this->end_date));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":instructor_id", $this->instructor_id);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":start_date", $this->start_date);
        $stmt->bindParam(":end_date", $this->end_date);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT c.id, c.title, c.description, c.status, c.start_date, c.end_date,
                         u.first_name, u.last_name, c.created_at
                 FROM " . $this->table_name . " c
                 LEFT JOIN users u ON c.instructor_id = u.id
                 ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function readPublished() {
        $query = "SELECT c.id, c.title, c.description, c.start_date, c.end_date,
                         u.first_name, u.last_name, c.created_at
                 FROM " . $this->table_name . " c
                 LEFT JOIN users u ON c.instructor_id = u.id
                 WHERE c.status = 'publicado'
                 ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function readByInstructor($instructor_id) {
        $query = "SELECT id, title, description, status, start_date, end_date, created_at
                 FROM " . $this->table_name . "
                 WHERE instructor_id = ?
                 ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $instructor_id);
        $stmt->execute();

        return $stmt;
    }

    public function readOne() {
        $query = "SELECT c.title, c.description, c.instructor_id, c.status, c.start_date, c.end_date,
                         u.first_name, u.last_name, c.created_at
                 FROM " . $this->table_name . " c
                 LEFT JOIN users u ON c.instructor_id = u.id
                 WHERE c.id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->instructor_id = $row['instructor_id'];
            $this->status = $row['status'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->created_at = $row['created_at'];
        }
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET title = :title, description = :description, status = :status,
                    start_date = :start_date, end_date = :end_date
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->end_date = htmlspecialchars(strip_tags($this->end_date));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
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

    public function getEnrolledStudents() {
        $query = "SELECT u.id, u.first_name, u.last_name, u.email, e.enrollment_date, e.status
                 FROM enrollments e
                 JOIN users u ON e.user_id = u.id
                 WHERE e.course_id = ?
                 ORDER BY e.enrollment_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        return $stmt;
    }
}
?>