<?php
require_once 'config/database.php';
require_once 'classes/Course.php';
require_once 'classes/Enrollment.php';
require_once 'includes/auth.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();
$course = new Course($db);
$enrollment = new Enrollment($db);

$page_title = "Cursos Disponíveis - Plataforma EAD";
include 'includes/header.php';

$available_courses = $course->readPublished();

// Processar inscrição
if ($_POST && isset($_POST['enroll'])) {
    $course_id = $_POST['course_id'];
    
    if (!$enrollment->isEnrolled(getCurrentUserId(), $course_id)) {
        $enrollment->user_id = getCurrentUserId();
        $enrollment->course_id = $course_id;
        $enrollment->status = 'inscrito';
        
        if ($enrollment->create()) {
            $success_message = "Inscrição realizada com sucesso!";
        } else {
            $error_message = "Erro ao realizar inscrição.";
        }
    } else {
        $error_message = "Você já está inscrito neste curso.";
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <i class="fas fa-search"></i> Cursos Disponíveis
            </h1>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php if ($available_courses->rowCount() > 0): ?>
            <?php while ($row = $available_courses->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-book"></i> <?php echo htmlspecialchars($row['title']); ?>
                            </h5>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> 
                                    <strong>Instrutor:</strong> <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                </small>
                            </div>
                            
                            <?php if ($row['start_date']): ?>
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> 
                                        <strong>Início:</strong> <?php echo date('d/m/Y', strtotime($row['start_date'])); ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($row['end_date']): ?>
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-check"></i> 
                                        <strong>Término:</strong> <?php echo date('d/m/Y', strtotime($row['end_date'])); ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-auto">
                                <?php if ($enrollment->isEnrolled(getCurrentUserId(), $row['id'])): ?>
                                    <div class="d-grid gap-2">
                                        <span class="badge bg-success p-2">
                                            <i class="fas fa-check"></i> Já Inscrito
                                        </span>
                                        <a href="view_course.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-eye"></i> Acessar Curso
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="d-grid gap-2">
                                        <a href="view_course.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i> Ver Detalhes
                                        </a>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="course_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="enroll" class="btn btn-success w-100" 
                                                    onclick="return confirm('Deseja se inscrever neste curso?')">
                                                <i class="fas fa-user-plus"></i> Inscrever-se
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-search fa-4x text-muted mb-3"></i>
                        <h4>Nenhum curso disponível</h4>
                        <p class="text-muted">Não há cursos publicados no momento. Volte mais tarde!</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>