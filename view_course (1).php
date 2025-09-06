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

$course_id = $_GET['id'] ?? 0;
$course->id = $course_id;
$course->readOne();

// Verificar se o usuário pode acessar este curso
$can_access = false;
if (isAdmin()) {
    $can_access = true;
} elseif (isInstructor() && $course->instructor_id == getCurrentUserId()) {
    $can_access = true;
} elseif (isStudent() && $enrollment->isEnrolled(getCurrentUserId(), $course_id)) {
    $can_access = true;
}

if (!$can_access && isStudent()) {
    // Permitir visualização para inscrição
    $preview_mode = true;
}

$lessons = $lesson->readByCourse($course_id);

// Processar inscrição
if ($_POST && isset($_POST['enroll'])) {
    if (!$enrollment->isEnrolled(getCurrentUserId(), $course_id)) {
        $enrollment->user_id = getCurrentUserId();
        $enrollment->course_id = $course_id;
        $enrollment->status = 'inscrito';
        
        if ($enrollment->create()) {
            header("Location: view_course.php?id=" . $course_id);
            exit();
        }
    }
}

$page_title = htmlspecialchars($course->title) . " - Plataforma EAD";
include 'includes/header.php';
?>

<div class="container-fluid">
    <?php if ($preview_mode ?? false): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            <strong>Modo Visualização:</strong> Inscreva-se no curso para acessar todo o conteúdo.
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="h4 mb-0">
                        <i class="fas fa-book"></i> <?php echo htmlspecialchars($course->title); ?>
                    </h2>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5>Descrição do Curso</h5>
                        <p><?php echo nl2br(htmlspecialchars($course->description)); ?></p>
                    </div>

                    <div class="row mb-4">
                        <?php if ($course->start_date): ?>
                            <div class="col-md-6">
                                <strong><i class="fas fa-calendar"></i> Data de Início:</strong>
                                <?php echo date('d/m/Y', strtotime($course->start_date)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($course->end_date): ?>
                            <div class="col-md-6">
                                <strong><i class="fas fa-calendar-check"></i> Data de Término:</strong>
                                <?php echo date('d/m/Y', strtotime($course->end_date)); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($preview_mode ?? false): ?>
                        <div class="text-center">
                            <form method="POST" class="d-inline">
                                <button type="submit" name="enroll" class="btn btn-success btn-lg">
                                    <i class="fas fa-user-plus"></i> Inscrever-se neste Curso
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> Informações do Curso</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Buscar informações do instrutor
                    $stmt = $db->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
                    $stmt->execute([$course->instructor_id]);
                    $instructor = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    
                    <div class="mb-3">
                        <strong><i class="fas fa-user"></i> Instrutor:</strong><br>
                        <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>
                    </div>
                    
                    <div class="mb-3">
                        <strong><i class="fas fa-calendar-plus"></i> Criado em:</strong><br>
                        <?php echo date('d/m/Y H:i', strtotime($course->created_at)); ?>
                    </div>
                    
                    <div class="mb-3">
                        <strong><i class="fas fa-flag"></i> Status:</strong><br>
                        <span class="badge bg-<?php echo $course->status === 'publicado' ? 'success' : ($course->status === 'rascunho' ? 'warning' : 'secondary'); ?>">
                            <?php echo ucfirst($course->status); ?>
                        </span>
                    </div>

                    <?php if ($can_access): ?>
                        <div class="mb-3">
                            <strong><i class="fas fa-list"></i> Total de Aulas:</strong><br>
                            <?php echo $lessons->rowCount(); ?> aula(s)
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($can_access): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>
                            <i class="fas fa-list"></i> Aulas do Curso
                            <?php if (isInstructor() && $course->instructor_id == getCurrentUserId()): ?>
                                <a href="course_lessons.php?id=<?php echo $course_id; ?>" class="btn btn-sm btn-primary float-end">
                                    <i class="fas fa-cog"></i> Gerenciar Aulas
                                </a>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($lessons->rowCount() > 0): ?>
                            <div class="list-group">
                                <?php while ($lesson_row = $lessons->fetch(PDO::FETCH_ASSOC)): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="ms-2 me-auto">
                                            <div class="fw-bold">
                                                <i class="fas fa-play-circle"></i> 
                                                Aula <?php echo $lesson_row['order']; ?>: <?php echo htmlspecialchars($lesson_row['title']); ?>
                                            </div>
                                            <small class="text-muted">
                                                Criada em <?php echo date('d/m/Y', strtotime($lesson_row['created_at'])); ?>
                                            </small>
                                        </div>
                                        <a href="view_lesson.php?id=<?php echo $lesson_row['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i> Ver Aula
                                        </a>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center">
                                <i class="fas fa-list fa-3x text-muted mb-3"></i>
                                <h5>Nenhuma aula disponível</h5>
                                <p class="text-muted">
                                    <?php if (isInstructor() && $course->instructor_id == getCurrentUserId()): ?>
                                        Você ainda não criou aulas para este curso.
                                    <?php else: ?>
                                        O instrutor ainda não adicionou aulas a este curso.
                                    <?php endif; ?>
                                </p>
                                <?php if (isInstructor() && $course->instructor_id == getCurrentUserId()): ?>
                                    <a href="course_lessons.php?id=<?php echo $course_id; ?>" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Adicionar Primeira Aula
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>