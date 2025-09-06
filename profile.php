<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'includes/auth.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$user->id = getCurrentUserId();
$user->readOne();

$message = '';
$message_type = '';

if ($_POST) {
    $user->first_name = $_POST['first_name'] ?? '';
    $user->last_name = $_POST['last_name'] ?? '';
    $user->email = $_POST['email'] ?? '';
    $user->username = $_POST['username'] ?? '';
    
    if (empty($user->first_name)) {
        $message = 'O nome é obrigatório.';
        $message_type = 'danger';
    } elseif (empty($user->last_name)) {
        $message = 'O sobrenome é obrigatório.';
        $message_type = 'danger';
    } elseif (empty($user->email)) {
        $message = 'O e-mail é obrigatório.';
        $message_type = 'danger';
    } elseif (empty($user->username)) {
        $message = 'O nome de usuário é obrigatório.';
        $message_type = 'danger';
    } else {
        if ($user->update()) {
            $message = 'Perfil atualizado com sucesso!';
            $message_type = 'success';
            
            // Atualizar sessão
            $_SESSION['user_name'] = $user->first_name . ' ' . $user->last_name;
            $_SESSION['username'] = $user->username;
            $_SESSION['user_email'] = $user->email;
        } else {
            $message = 'Erro ao atualizar perfil. Usuário ou e-mail já existe.';
            $message_type = 'danger';
        }
    }
}

$page_title = "Meu Perfil - Plataforma EAD";
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <i class="fas fa-user-edit"></i> Meu Perfil
            </h1>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-user"></i> Informações Pessoais</h5>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Nome *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($user->first_name); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Sobrenome *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($user->last_name); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Nome de Usuário *</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($user->username); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">E-mail *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user->email); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Tipo de Conta</label>
                            <input type="text" class="form-control" id="role" 
                                   value="<?php echo ucfirst($user->role); ?>" readonly>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> 
                                Para alterar o tipo de conta, entre em contato com o administrador.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="created_at" class="form-label">Membro desde</label>
                            <input type="text" class="form-control" id="created_at" 
                                   value="<?php echo date('d/m/Y H:i', strtotime($user->created_at)); ?>" readonly>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Estatísticas do usuário -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar"></i> Suas Estatísticas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if (isStudent()): ?>
                            <?php
                            // Estatísticas do estudante
                            $stmt = $db->prepare("SELECT COUNT(*) as total FROM enrollments WHERE user_id = ?");
                            $stmt->execute([getCurrentUserId()]);
                            $total_enrollments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                            
                            $stmt = $db->prepare("SELECT COUNT(*) as total FROM enrollments WHERE user_id = ? AND status = 'concluido'");
                            $stmt->execute([getCurrentUserId()]);
                            $completed_courses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                            ?>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h4><?php echo $total_enrollments; ?></h4>
                                        <p class="mb-0">Cursos Inscritos</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h4><?php echo $completed_courses; ?></h4>
                                        <p class="mb-0">Cursos Concluídos</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h4><?php echo $total_enrollments - $completed_courses; ?></h4>
                                        <p class="mb-0">Em Andamento</p>
                                    </div>
                                </div>
                            </div>
                        <?php elseif (isInstructor()): ?>
                            <?php
                            // Estatísticas do instrutor
                            $stmt = $db->prepare("SELECT COUNT(*) as total FROM courses WHERE instructor_id = ?");
                            $stmt->execute([getCurrentUserId()]);
                            $total_courses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                            
                            $stmt = $db->prepare("SELECT COUNT(*) as total FROM enrollments e 
                                                 JOIN courses c ON e.course_id = c.id 
                                                 WHERE c.instructor_id = ?");
                            $stmt->execute([getCurrentUserId()]);
                            $total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                            
                            $stmt = $db->prepare("SELECT COUNT(*) as total FROM lessons l 
                                                 JOIN courses c ON l.course_id = c.id 
                                                 WHERE c.instructor_id = ?");
                            $stmt->execute([getCurrentUserId()]);
                            $total_lessons = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                            ?>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h4><?php echo $total_courses; ?></h4>
                                        <p class="mb-0">Cursos Criados</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h4><?php echo $total_students; ?></h4>
                                        <p class="mb-0">Total de Alunos</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h4><?php echo $total_lessons; ?></h4>
                                        <p class="mb-0">Aulas Criadas</p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php
                            // Estatísticas do administrador
                            $stmt = $db->query("SELECT COUNT(*) as total FROM users");
                            $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                            
                            $stmt = $db->query("SELECT COUNT(*) as total FROM courses");
                            $total_courses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                            
                            $stmt = $db->query("SELECT COUNT(*) as total FROM enrollments");
                            $total_enrollments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                            ?>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h4><?php echo $total_users; ?></h4>
                                        <p class="mb-0">Total de Usuários</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h4><?php echo $total_courses; ?></h4>
                                        <p class="mb-0">Total de Cursos</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h4><?php echo $total_enrollments; ?></h4>
                                        <p class="mb-0">Total de Inscrições</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>