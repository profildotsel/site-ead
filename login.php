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

$login_error = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($user->login($username, $password)) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['user_name'] = $user->first_name . ' ' . $user->last_name;
        $_SESSION['user_role'] = $user->role;
        $_SESSION['user_email'] = $user->email;
        
        header("Location: dashboard.php");
        exit();
    } else {
        $login_error = 'Usuário ou senha incorretos.';
    }
}

$page_title = "Login - Plataforma EAD";
include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                        <h3>Entrar na Plataforma</h3>
                    </div>
                    
                    <?php if ($login_error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $login_error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="fas fa-user"></i> Usuário
                            </label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Senha
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Entrar
                            </button>
                        </div>
                    </form>
                    
                    <hr>
                    
                    <div class="text-center">
                        <p class="mb-0">Não tem uma conta?</p>
                        <a href="register.php" class="btn btn-outline-primary">
                            <i class="fas fa-user-plus"></i> Cadastrar-se
                        </a>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            <strong>Contas de teste:</strong><br>
                            Admin: admin / admin123<br>
                            Professor: professor / prof123<br>
                            Aluno: aluno / aluno123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>