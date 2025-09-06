<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Course.php';
require_once 'classes/Enrollment.php';
require_once 'includes/auth.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();
$course = new Course($db);
$enrollment = new Enrollment($db);

$page_title = "Dashboard - Plataforma EAD";
include 'includes/header.php';

// Estatísticas baseadas no papel do usuário
if (isAdmin()) {
    // Admin vê estatísticas gerais
    $stmt = $db->query("SELECT COUNT(*) as total FROM users");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM courses");
    $total_courses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM enrollments");
    $total_enrollments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $recent_courses = $course->readAll();
    
} elseif (isInstructor()) {
    // Instrutor vê seus cursos
    $my_courses = $course->readByInstructor(getCurrentUserId());
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM courses WHERE instructor_id = ?");
    $stmt->execute([getCurrentUserId()]);
    $total_my_courses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM enrollments e 
                         JOIN courses c ON e.course_id = c.id 
                         WHERE c.instructor_id = ?");
    $stmt->execute([getCurrentUserId()]);
    $total_my_students = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
} else {
    // Estudante vê seus cursos
    $my_courses = $enrollment->getUserCourses(getCurrentUserId());
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM enrollments WHERE user_id = ?");
    $stmt->execute([getCurrentUserId()]);
    $total_my_enrollments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $available_courses = $course->readPublished();
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <i class="fas fa-tachometer-alt"></i> Dashboard
                <small class="text-muted">- Bem-vindo, <?php echo getCurrentUserName(); ?>!</small>
            </h1>
        </div>
    </div>

    <?php if (isAdmin()): ?>
    <!-- Admin Dashboard -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $total_users; ?></h4>
                            <p class="mb-0">Total de Usuários</p>
                        </div>
                        <div>
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $total_courses; ?></h4>
                            <p class="mb-0">Total de Cursos</p>
                        </div>
                        <div>
                            <i class="fas fa-book fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $total_enrollments; ?></h4>
                            <p class="mb-0">Total de Inscrições</p>
                        </div>
                        <div>
                            <i class="fas fa-user-graduate fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>Sistema</h4>
                            <p class="mb-0">Funcionando</p>
                        </div>
                        <div>
                            <i class="fas fa-cog fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list"></i> Cursos Recentes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Curso</th>
                                    <th>Instrutor</th>
                                    <th>Status</th>
                                    <th>Data Criação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $recent_courses->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $row['status'] === 'publicado' ? 'success' : ($row['status'] === 'rascunho' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <a href="view_course.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php elseif (isInstructor()): ?>
    <!-- Instructor Dashboard -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $total_my_courses; ?></h4>
                            <p class="mb-0">Meus Cursos</p>
                        </div>
                        <div>
                            <i class="fas fa-chalkboard-teacher fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $total_my_students; ?></h4>
                            <p class="mb-0">Total de Alunos</p>
                        </div>
                        <div>
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <a href="create_course.php" class="text-white text-decoration-none">
                        <i class="fas fa-plus fa-3x mb-2"></i>
                        <p class="mb-0">Criar Novo Curso</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-book"></i> Meus Cursos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Curso</th>
                                    <th>Status</th>
                                    <th>Data Início</th>
                                    <th>Data Fim</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $my_courses->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $row['status'] === 'publicado' ? 'success' : ($row['status'] === 'rascunho' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row['start_date'] ? date('d/m/Y', strtotime($row['start_date'])) : '-'; ?></td>
                                    <td><?php echo $row['end_date'] ? date('d/m/Y', strtotime($row['end_date'])) : '-'; ?></td>
                                    <td>
                                        <a href="edit_course.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <a href="course_lessons.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-list"></i> Aulas
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- Student Dashboard -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $total_my_enrollments; ?></h4>
                            <p class="mb-0">Cursos Inscritos</p>
                        </div>
                        <div>
                            <i class="fas fa-book fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <a href="available_courses.php" class="text-white text-decoration-none">
                        <i class="fas fa-search fa-3x mb-2"></i>
                        <p class="mb-0">Buscar Cursos</p>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <a href="my_courses.php" class="text-white text-decoration-none">
                        <i class="fas fa-graduation-cap fa-3x mb-2"></i>
                        <p class="mb-0">Meus Cursos</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-book-open"></i> Meus Cursos</h5>
                </div>
                <div class="card-body">
                    <?php if ($my_courses->rowCount() > 0): ?>
                        <?php while ($row = $my_courses->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="card mb-3 border-left-primary">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h6>
                                <p class="card-text text-muted">
                                    <small>Instrutor: <?php echo htmlspecialchars($row['instructor_name'] . ' ' . $row['instructor_lastname']); ?></small>
                                </p>
                                <span class="badge bg-<?php echo $row['enrollment_status'] === 'inscrito' ? 'primary' : ($row['enrollment_status'] === 'concluido' ? 'success' : 'secondary'); ?>">
                                    <?php echo ucfirst($row['enrollment_status']); ?>
                                </span>
                                <a href="view_course.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary float-end">
                                    <i class="fas fa-eye"></i> Acessar
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted">Você ainda não está inscrito em nenhum curso.</p>
                        <a href="available_courses.php" class="btn btn-primary">
                            <i class="fas fa-search"></i> Buscar Cursos
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-star"></i> Cursos Recomendados</h5>
                </div>
                <div class="card-body">
                    <?php $available_courses->execute(); ?>
                    <?php $count = 0; ?>
                    <?php while ($row = $available_courses->fetch(PDO::FETCH_ASSOC) && $count < 3): ?>
                    <?php if (!$enrollment->isEnrolled(getCurrentUserId(), $row['id'])): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h6>
                                <p class="card-text"><?php echo htmlspecialchars(substr($row['description'], 0, 100)) . '...'; ?></p>
                                <small class="text-muted">
                                    Instrutor: <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                </small>
                                <a href="view_course.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary float-end">
                                    <i class="fas fa-eye"></i> Ver Detalhes
                                </a>
                            </div>
                        </div>
                        <?php $count++; ?>
                    <?php endif; ?>
                    <?php endwhile; ?>
                    
                    <a href="available_courses.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> Ver Todos os Cursos
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>