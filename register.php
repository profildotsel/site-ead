<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$message = '';
$message_type = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = $_POST['email'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $role = $_POST['role'] ?? 'estudante';
    
    if ($password !== $confirm_password) {
        $message = 'As senhas não coincidem.';
        $message_type = 'danger';
    } elseif (strlen($password) < 6) {
        $message = 'A senha deve ter pelo menos 6 caracteres.';
        $message_type = 'danger';
    } else {
        $user->username = $username;
        $user->password_hash = $password; // Em produção, usar password_hash()
        $user->email = $email;
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->role = $role;
        
        if ($user->create()) {
            $message = 'Conta criada com sucesso! Você pode fazer login agora.';
            $message_type = 'success';
        } else {
            $message = 'Erro ao criar conta. Usuário ou email já existe.';
            $message_type = 'danger';
        }
    }
}

$page_title = "Cadastro - Plataforma EAD";
include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center align-items-center py-5">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                        <h3>Criar Conta</h3>
                    </div>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                            <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo $_POST['first_name'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Sobrenome</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo $_POST['last_name'] ?? ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Usuário</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo $_POST['username'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo $_POST['email'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Tipo de Conta</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="estudante" <?php echo ($_POST['role'] ?? '') === 'estudante' ? 'selected' : ''; ?>>
                                    Estudante
                                </option>
                                <option value="instrutor" <?php echo ($_POST['role'] ?? '') === 'instrutor' ? 'selected' : ''; ?>>
                                    Instrutor
                                </option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="confirm_password" class="form-label">Confirmar Senha</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus"></i> Criar Conta
                            </button>
                        </div>
                    </form>
                    
                    <hr>
                    
                    <div class="text-center">
                        <p class="mb-0">Já tem uma conta?</p>
                        <a href="login.php" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt"></i> Entrar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>