<?php
require_once 'config/database.php';
require_once 'classes/Course.php';
require_once 'classes/Lesson.php';
require_once 'classes/Enrollment.php';
require_once 'includes/auth.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();
$course = new Course($db);
$lesson = new Lesson($db);
$enrollment = new Enrollment($db);

$lesson_id = $_GET['id'] ?? 0;
$lesson->id = $lesson_id;
$lesson->readOne();

$course->id = $lesson->course_id;
$course->readOne();

// Verificar se o usuário pode acessar esta aula
$can_access = false;
if (isAdmin()) {
    $can_access = true;
} elseif (isInstructor() && $course->instructor_id == getCurrentUserId()) {
    $can_access = true;
} elseif (isStudent() && $enrollment->isEnrolled(getCurrentUserId(), $lesson->course_id)) {
    $can_access = true;
}

if (!$can_access) {
    header("Location: access_denied.php");
    exit();
}

// Buscar outras aulas do curso para navegação
$all_lessons = $lesson->readByCourse($lesson->course_id);
$lessons_array = [];
$current_index = 0;

while ($row = $all_lessons->fetch(PDO::FETCH_ASSOC)) {
    $lessons_array[] = $row;
    if ($row['id'] == $lesson_id) {
        $current_index = count($lessons_array) - 1;
    }
}

$prev_lesson = $current_index > 0 ? $lessons_array[$current_index - 1] : null;
$next_lesson = $current_index < count($lessons_array) - 1 ? $lessons_array[$current_index + 1] : null;

$page_title = htmlspecialchars($lesson->title) . " - " . htmlspecialchars($course->title);
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-play-circle"></i> 
                                Aula <?php echo $lesson->order; ?>: <?php echo htmlspecialchars($lesson->title); ?>
                            </h4>
                            <small class="text-muted">
                                Curso: <a href="view_course.php?id=<?php echo $course->id; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($course->title); ?>
                                </a>
                            </small>
                        </div>
                        <?php if (isInstructor() && $course->instructor_id == getCurrentUserId()): ?>
                            <div>
                                <a href="edit_lesson.php?id=<?php echo $lesson->id; ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="lesson-content">
                        <?php echo $lesson->content; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-6">
                            <?php if ($prev_lesson): ?>
                                <a href="view_lesson.php?id=<?php echo $prev_lesson['id']; ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-chevron-left"></i> Aula Anterior
                                    <br><small><?php echo htmlspecialchars($prev_lesson['title']); ?></small>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="col-6 text-end">
                            <?php if ($next_lesson): ?>
                                <a href="view_lesson.php?id=<?php echo $next_lesson['id']; ?>" class="btn btn-primary">
                                    Próxima Aula <i class="fas fa-chevron-right"></i>
                                    <br><small><?php echo htmlspecialchars($next_lesson['title']); ?></small>
                                </a>
                            <?php else: ?>
                                <div class="alert alert-success mb-0">
                                    <i class="fas fa-check-circle"></i> 
                                    <strong>Parabéns!</strong> Você completou todas as aulas deste curso.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h6><i class="fas fa-list"></i> Aulas do Curso</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($lessons_array as $index => $lesson_item): ?>
                            <div class="list-group-item <?php echo $lesson_item['id'] == $lesson_id ? 'active' : ''; ?>">
                                <?php if ($lesson_item['id'] == $lesson_id): ?>
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <i class="fas fa-play-circle"></i> 
                                            Aula <?php echo $lesson_item['order']; ?>
                                        </h6>
                                        <small><i class="fas fa-eye"></i></small>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($lesson_item['title']); ?></p>
                                    <small>Aula atual</small>
                                <?php else: ?>
                                    <a href="view_lesson.php?id=<?php echo $lesson_item['id']; ?>" class="text-decoration-none text-dark">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">
                                                <i class="fas fa-play-circle"></i> 
                                                Aula <?php echo $lesson_item['order']; ?>
                                            </h6>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars($lesson_item['title']); ?></p>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="progress mb-2">
                        <?php 
                        $progress = (($current_index + 1) / count($lessons_array)) * 100;
                        ?>
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%" 
                             aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted">
                        Progresso: <?php echo $current_index + 1; ?> de <?php echo count($lessons_array); ?> aulas
                        (<?php echo number_format($progress, 1); ?>%)
                    </small>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6><i class="fas fa-info-circle"></i> Informações</h6>
                </div>
                <div class="card-body">
                    <p><strong>Aula criada em:</strong><br>
                       <?php echo date('d/m/Y H:i', strtotime($lesson->created_at)); ?></p>
                    
                    <p><strong>Curso:</strong><br>
                       <a href="view_course.php?id=<?php echo $course->id; ?>" class="text-decoration-none">
                           <?php echo htmlspecialchars($course->title); ?>
                       </a></p>
                    
                    <?php if (isStudent()): ?>
                        <div class="d-grid">
                            <a href="view_course.php?id=<?php echo $course->id; ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-arrow-left"></i> Voltar ao Curso
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="d-grid">
                            <a href="course_lessons.php?id=<?php echo $course->id; ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-cog"></i> Gerenciar Aulas
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.lesson-content {
    font-size: 1.1em;
    line-height: 1.6;
}

.lesson-content h1, .lesson-content h2, .lesson-content h3 {
    color: #333;
    margin-top: 1.5em;
    margin-bottom: 0.8em;
}

.lesson-content h1 {
    border-bottom: 2px solid #007bff;
    padding-bottom: 0.3em;
}

.lesson-content h2 {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 0.2em;
}

.lesson-content p {
    margin-bottom: 1em;
}

.lesson-content ul, .lesson-content ol {
    margin-bottom: 1em;
    padding-left: 2em;
}

.lesson-content li {
    margin-bottom: 0.5em;
}

.lesson-content code {
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.9em;
}

.lesson-content pre {
    background-color: #f8f9fa;
    padding: 1em;
    border-radius: 5px;
    overflow-x: auto;
}

.lesson-content blockquote {
    border-left: 4px solid #007bff;
    margin: 1em 0;
    padding-left: 1em;
    color: #6c757d;
}
</style>

<?php include 'includes/footer.php'; ?>