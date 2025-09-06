<?php
class Enrollment {
    private $conn;
    private $table_name = "enrollments";

    public $id;
    public $user_id;
    public $course_id;
    public $enrollment_date;
    public $completion_date;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET user_id=:user_id, course_id=:course_id, status=:status";

        $stmt = $this->conn->prepare($query);

        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->course_id = htmlspecialchars(strip_tags($this->course_id));
        $this->status = htmlspecialchars(strip_tags($this->status));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getUserCourses($user_id) {
        $query = "SELECT c.id, c.title, c.description, c.start_date, c.end_date,
                         u.first_name as instructor_name, u.last_name as instructor_lastname,
                         e.enrollment_date, e.status as enrollment_status
                 FROM " . $this->table_name . " e
                 JOIN courses c ON e.course_id = c.id
                 JOIN users u ON c.instructor_id = u.id
                 WHERE e.user_id = ?
                 ORDER BY e.enrollment_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();

        return $stmt;
    }

    public function isEnrolled($user_id, $course_id) {
        $query = "SELECT id FROM " . $this->table_name . "
                 WHERE user_id = ? AND course_id = ? LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $course_id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status
                WHERE user_id = :user_id AND course_id = :course_id";

        $stmt = $this->conn->prepare($query);

        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->course_id = htmlspecialchars(strip_tags($this->course_id));

        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':course_id', $this->course_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>