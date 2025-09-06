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

$page_title = "Meus Cursos - Plataforma EAD";
include 'includes/header.php';

if (isStudent()) {
    $my_courses = $enrollment->getUserCourses(getCurrentUserId());
} else {
    $my_courses = $course->readByInstructor(getCurrentUserId());
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <i class="fas fa-book"></i> Meus Cursos
                <?php if (isInstructor()): ?>
                    <a href="create_course.php" class="btn btn-primary float-end">
                        <i class="fas fa-plus"></i> Criar Novo Curso
                    </a>
                <?php endif; ?>
            </h1>
        </div>
    </div>

    <div class="row">
        <?php if ($my_courses->rowCount() > 0): ?>
            <?php while ($row = $my_courses->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-<?php echo isStudent() ? 'info' : 'primary'; ?> text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-book"></i> <?php echo htmlspecialchars($row['title']); ?>
                            </h5>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <p class="card-text"><?php echo htmlspecialchars($row['description'] ?? ''); ?></p>
                            
                            <?php if (isStudent()): ?>
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> 
                                        <strong>Instrutor:</strong> <?php echo htmlspecialchars($row['instructor_name'] . ' ' . $row['instructor_lastname']); ?>
                                    </small>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> 
                                        <strong>Inscrição:</strong> <?php echo date('d/m/Y', strtotime($row['enrollment_date'])); ?>
                                    </small>
                                </div>
                                
                                <div class="mb-3">
                                    <span class="badge bg-<?php echo $row['enrollment_status'] === 'inscrito' ? 'primary' : ($row['enrollment_status'] === 'concluido' ? 'success' : 'secondary'); ?>">
                                        <?php echo ucfirst($row['enrollment_status']); ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <span class="badge bg-<?php echo $row['status'] === 'publicado' ? 'success' : ($row['status'] === 'rascunho' ? 'warning' : 'secondary'); ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </div>
                                
                                <?php if ($row['start_date']): ?>
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> 
                                            <strong>Início:</strong> <?php echo date('d/m/Y', strtotime($row['start_date'])); ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <div class="mt-auto">
                                <?php if (isStudent()): ?>
                                    <div class="d-grid">
                                        <a href="view_course.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-eye"></i> Acessar Curso
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="d-grid gap-2">
                                        <a href="edit_course.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <a href="course_lessons.php?id=<?php echo $row['id']; ?>" class="btn btn-info">
                                            <i class="fas fa-list"></i> Gerenciar Aulas
                                        </a>
                                        <a href="view_course.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-eye"></i> Visualizar
                                        </a>
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
                        <i class="fas fa-book fa-4x text-muted mb-3"></i>
                        <h4>Nenhum curso encontrado</h4>
                        <?php if (isStudent()): ?>
                            <p class="text-muted">Você ainda não está inscrito em nenhum curso.</p>
                            <a href="available_courses.php" class="btn btn-primary">
                                <i class="fas fa-search"></i> Buscar Cursos
                            </a>
                        <?php else: ?>
                            <p class="text-muted">Você ainda não criou nenhum curso.</p>
                            <a href="create_course.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Criar Primeiro Curso
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>