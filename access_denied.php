<?php
require_once 'includes/auth.php';

$page_title = "Acesso Negado - Plataforma EAD";
include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-body p-5">
                    <i class="fas fa-lock fa-4x text-danger mb-4"></i>
                    <h2 class="text-danger">Acesso Negado</h2>
                    <p class="lead mb-4">
                        Você não tem permissão para acessar esta página.
                    </p>
                    <p class="text-muted mb-4">
                        Esta área é restrita a usuários com privilégios específicos. 
                        Se você acredita que deveria ter acesso, entre em contato com o administrador.
                    </p>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <?php if (isLoggedIn()): ?>
                            <a href="dashboard.php" class="btn btn-primary">
                                <i class="fas fa-tachometer-alt"></i> Ir para Dashboard
                            </a>
                            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Fazer Login
                            </a>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-home"></i> Página Inicial
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>