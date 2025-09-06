<?php
require_once 'config/database.php';
require_once 'classes/Lesson.php';
require_once 'includes/auth.php';

requireRole('administrador');

header('Content-Type: application/json');

if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    echo json_encode([]);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$lesson = new Lesson($db);

$lesson->course_id = $_GET['course_id'];
$lessons = $lesson->readByCourse();

$result = [];
while ($row = $lessons->fetch(PDO::FETCH_ASSOC)) {
    $result[] = [
        'id' => $row['id'],
        'title' => htmlspecialchars($row['title']),
        'content' => htmlspecialchars($row['content']),
        'video_url' => htmlspecialchars($row['video_url']),
        'order_number' => $row['order_number']
    ];
}

echo json_encode($result);
?>