<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$page_title = "Bem-vindo à Plataforma EAD";
include 'includes/header.php';
?>

<div class="container-fluid vh-100">
    <div class="row h-100">
        <div class="col-lg-6 d-flex align-items-center justify-content-center">
            <div class="text-center">
                <h1 class="display-4 fw-bold text-primary mb-4">
                    <i class="fas fa-graduation-cap"></i> Plataforma EAD
                </h1>
                <p class="lead mb-4">
                    Sua jornada de aprendizado começa aqui. Acesse cursos de qualidade, 
                    interaja com professores especializados e desenvolva suas habilidades.
                </p>
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <a href="login.php" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-sign-in-alt"></i> Entrar
                    </a>
                    <a href="register.php" class="btn btn-outline-primary btn-lg px-4">
                        <i class="fas fa-user-plus"></i> Cadastrar-se
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-6 bg-primary d-flex align-items-center justify-content-center">
            <div class="text-center text-white p-5">
                <h2 class="mb-4">Por que escolher nossa plataforma?</h2>
                <div class="row">
                    <div class="col-12 mb-4">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h5>Múltiplos Usuários</h5>
                        <p>Sistema completo para alunos, professores e administradores</p>
                    </div>
                    <div class="col-12 mb-4">
                        <i class="fas fa-book-open fa-3x mb-3"></i>
                        <h5>Cursos Interativos</h5>
                        <p>Aulas dinâmicas com recursos multimídia e atividades práticas</p>
                    </div>
                    <div class="col-12 mb-4">
                        <i class="fas fa-certificate fa-3x mb-3"></i>
                        <h5>Certificados</h5>
                        <p>Obtenha certificados ao concluir os cursos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>