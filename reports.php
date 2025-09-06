<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Course.php';
require_once 'includes/auth.php';

requireRole('administrador');

$database = new Database();
$db = $database->getConnection();

$page_title = "Relatórios - Plataforma EAD";
include 'includes/header.php';

// Estatísticas gerais
$stmt = $db->query("SELECT COUNT(*) as total FROM users");
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'estudante'");
$total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'instrutor'");
$total_instructors = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM courses");
$total_courses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM courses WHERE status = 'publicado'");
$published_courses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM enrollments");
$total_enrollments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM enrollments WHERE status = 'concluido'");
$completed_enrollments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM lessons");
$total_lessons = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Cursos mais populares
$popular_courses = $db->query("
    SELECT c.title, c.description, CONCAT(u.first_name, ' ', u.last_name) as instructor,
           COUNT(e.id) as enrollments_count
    FROM courses c
    LEFT JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN users u ON c.instructor_id = u.id
    WHERE c.status = 'publicado'
    GROUP BY c.id
    ORDER BY enrollments_count DESC
    LIMIT 5
");

// Instrutores mais ativos
$active_instructors = $db->query("
    SELECT CONCAT(u.first_name, ' ', u.last_name) as name, u.email,
           COUNT(c.id) as courses_count,
           COUNT(e.id) as total_students
    FROM users u
    LEFT JOIN courses c ON u.id = c.instructor_id
    LEFT JOIN enrollments e ON c.id = e.course_id
    WHERE u.role = 'instrutor'
    GROUP BY u.id
    ORDER BY courses_count DESC, total_students DESC
    LIMIT 5
");

// Usuários recentes
$recent_users = $db->query("
    SELECT CONCAT(first_name, ' ', last_name) as name, email, role, created_at
    FROM users
    ORDER BY created_at DESC
    LIMIT 10
");

// Estatísticas por mês
$monthly_stats = $db->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as new_users
    FROM users 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
");

$monthly_enrollments = $db->query("
    SELECT 
        DATE_FORMAT(enrollment_date, '%Y-%m') as month,
        COUNT(*) as new_enrollments
    FROM enrollments 
    WHERE enrollment_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(enrollment_date, '%Y-%m')
    ORDER BY month DESC
");
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <i class="fas fa-chart-bar"></i> Relatórios do Sistema
            </h1>
        </div>
    </div>

    <!-- Estatísticas Gerais -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
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
        
        <div class="col-lg-3 col-md-6 mb-3">
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
        
        <div class="col-lg-3 col-md-6 mb-3">
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
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $total_lessons; ?></h4>
                            <p class="mb-0">Total de Aulas</p>
                        </div>
                        <div>
                            <i class="fas fa-play-circle fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas Detalhadas -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-users"></i> Distribuição de Usuários</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h3 class="text-primary"><?php echo $total_students; ?></h3>
                            <p class="mb-0">Estudantes</p>
                            <small class="text-muted"><?php echo $total_users > 0 ? number_format(($total_students/$total_users)*100, 1) : 0; ?>%</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-warning"><?php echo $total_instructors; ?></h3>
                            <p class="mb-0">Instrutores</p>
                            <small class="text-muted"><?php echo $total_users > 0 ? number_format(($total_instructors/$total_users)*100, 1) : 0; ?>%</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-danger">1</h3>
                            <p class="mb-0">Administradores</p>
                            <small class="text-muted"><?php echo $total_users > 0 ? number_format((1/$total_users)*100, 1) : 0; ?>%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie"></i> Status dos Cursos</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h3 class="text-success"><?php echo $published_courses; ?></h3>
                            <p class="mb-0">Publicados</p>
                            <small class="text-muted"><?php echo $total_courses > 0 ? number_format(($published_courses/$total_courses)*100, 1) : 0; ?>%</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-warning"><?php echo $total_courses - $published_courses; ?></h3>
                            <p class="mb-0">Rascunhos</p>
                            <small class="text-muted"><?php echo $total_courses > 0 ? number_format((($total_courses - $published_courses)/$total_courses)*100, 1) : 0; ?>%</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-info"><?php echo $completed_enrollments; ?></h3>
                            <p class="mb-0">Concluídos</p>
                            <small class="text-muted">por alunos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Cursos Mais Populares -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-star"></i> Cursos Mais Populares</h5>
                </div>
                <div class="card-body">
                    <?php if ($popular_courses->rowCount() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Curso</th>
                                        <th>Instrutor</th>
                                        <th>Inscrições</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $popular_courses->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['instructor']); ?></td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $row['enrollments_count']; ?></span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Nenhum curso publicado ainda.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Instrutores Mais Ativos -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chalkboard-teacher"></i> Instrutores Mais Ativos</h5>
                </div>
                <div class="card-body">
                    <?php if ($active_instructors->rowCount() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Instrutor</th>
                                        <th>Cursos</th>
                                        <th>Alunos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $active_instructors->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $row['courses_count']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success"><?php echo $row['total_students']; ?></span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Nenhum instrutor cadastrado ainda.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Usuários Recentes -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-user-plus"></i> Usuários Recentes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Papel</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $recent_users->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $row['role'] === 'administrador' ? 'danger' : 
                                                ($row['role'] === 'instrutor' ? 'warning' : 'primary'); 
                                        ?>">
                                            <?php echo ucfirst($row['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estatísticas Mensais -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-calendar"></i> Crescimento Mensal</h5>
                </div>
                <div class="card-body">
                    <h6>Novos Usuários por Mês:</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Mês</th>
                                    <th>Usuários</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $monthly_stats->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo date('m/Y', strtotime($row['month'] . '-01')); ?></td>
                                    <td><span class="badge bg-primary"><?php echo $row['new_users']; ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <h6>Novas Inscrições por Mês:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Mês</th>
                                    <th>Inscrições</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $monthly_enrollments->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo date('m/Y', strtotime($row['month'] . '-01')); ?></td>
                                    <td><span class="badge bg-success"><?php echo $row['new_enrollments']; ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-download"></i> Exportar Relatórios</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Funcionalidades de exportação podem ser implementadas aqui.</p>
                    <button class="btn btn-outline-primary me-2" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir Relatório
                    </button>
                    <button class="btn btn-outline-success" onclick="alert('Funcionalidade de exportação CSV em desenvolvimento')">
                        <i class="fas fa-file-csv"></i> Exportar CSV
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .card-header .btn, .navbar, .sidebar {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>