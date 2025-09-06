<?php
require_once 'config/database.php';
require_once 'classes/Course.php';
require_once 'classes/User.php';
require_once 'classes/Lesson.php';
require_once 'includes/auth.php';

$database = new Database();
$db = $database->getConnection();

$course = new Course($db);
$lesson = new Lesson($db);
$user = new User($db);

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: courses.php');
    exit();
}

$course->id = $_GET['id'];
if (!$course->readOne() || !$course->title) {
    header('Location: courses.php');
    exit();
}

$page_title = htmlspecialchars($course->title);
include 'includes/header.php';

$instructor_name = "N/A";
if ($course->instructor_id) {
    $user->id = $course->instructor_id;
    $user->readOne();
    $instructor_name = htmlspecialchars($user->first_name . ' ' . $user->last_name);
}

$lesson->course_id = $course->id;
$lessons_stmt = $lesson->readByCourse();
$lessons_count = $lessons_stmt ? $lessons_stmt->rowCount() : 0;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <h1 class="h3 mb-4"><i class="fas fa-book-open"></i> <?php echo htmlspecialchars($course->title); ?></h1>

            <div class="card mb-4">
                <div class="card-header"><h5>Descrição</h5></div>
                <div class="card-body"><?php echo $course->description; ?></div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h5>Aulas do Curso</h5></div>
                <div class="card-body">
                    <?php if ($lessons_count > 0): ?>
                        <div class="accordion" id="lessonsAccordion">
                            <?php 
                            $lesson_num = 1;
                            $lessons = $lessons_stmt->fetchAll(PDO::FETCH_ASSOC);
                            // Ordena as aulas pelo número da ordem
                            usort($lessons, function($a, $b) {
                                return $a['order_number'] <=> $b['order_number'];
                            });

                            foreach ($lessons as $lesson_row): 
                            ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?php echo $lesson_row['id']; ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $lesson_row['id']; ?>">
                                            Aula <?php echo $lesson_num++; ?>: <?php echo htmlspecialchars($lesson_row['title']); ?>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $lesson_row['id']; ?>" class="accordion-collapse collapse" data-bs-parent="#lessonsAccordion">
                                        <div class="accordion-body">
                                            <?php if (!empty($lesson_row['video_url'])): ?>
                                                <div class="ratio ratio-16x9 mb-3">
                                                    <iframe src="<?php echo htmlspecialchars($lesson_row['video_url']); ?>" title="<?php echo htmlspecialchars($lesson_row['title']); ?>" frameborder="0" allowfullscreen></iframe>
                                                </div>
                                            <?php endif; ?>
                                            <div><?php echo $lesson_row['content']; ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4"><p class="lead">Nenhuma aula disponível para este curso.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5>Informações</h5></div>
                <div class="card-body">
                    <p><strong>Instrutor:</strong> <?php echo $instructor_name; ?></p>
                    <p><strong>Status:</strong> <span class="badge bg-<?php echo $course->status === 'publicado' ? 'success' : 'warning'; ?>"><?php echo ucfirst($course->status); ?></span></p>
                    <p><strong>Total de Aulas:</strong> <?php echo $lessons_count; ?></p>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'administrador'): ?>
                        <a href="manage_courses.php" class="btn btn-secondary w-100 mt-3"><i class="fas fa-arrow-left"></i> Voltar à Gerência</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>