<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Plataforma EAD'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 20px;
            width: 250px;
            z-index: 1000;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .sidebar .nav-link {
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 5px 10px;
        }
        .sidebar .nav-link:hover {
            background-color: #495057;
            color: #fff;
        }
        .sidebar .nav-link.active {
            background-color: #007bff;
            color: #fff;
        }
        .user-info {
            background-color: #495057;
            color: #fff;
            padding: 15px;
            margin: 10px;
            border-radius: 5px;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="user-info">
            <h6><i class="fas fa-user"></i> <?php echo getCurrentUserName(); ?></h6>
            <small class="text-muted"><?php echo ucfirst(getCurrentUserRole()); ?></small>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            
            <?php if (isStudent()): ?>
                <a class="nav-link" href="my_courses.php">
                    <i class="fas fa-book"></i> Meus Cursos
                </a>
                <a class="nav-link" href="available_courses.php">
                    <i class="fas fa-search"></i> Cursos Disponíveis
                </a>
            <?php endif; ?>
            
            <?php if (isInstructor()): ?>
                <a class="nav-link" href="my_courses.php">
                    <i class="fas fa-chalkboard-teacher"></i> Meus Cursos
                </a>
                <a class="nav-link" href="create_course.php">
                    <i class="fas fa-plus"></i> Criar Curso
                </a>
                <a class="nav-link" href="my_students.php">
                    <i class="fas fa-users"></i> Meus Alunos
                </a>
            <?php endif; ?>
            
            <?php if (isAdmin()): ?>
                <a class="nav-link" href="manage_users.php">
                    <i class="fas fa-users-cog"></i> Gerenciar Usuários
                </a>
                <a class="nav-link" href="manage_courses.php">
                    <i class="fas fa-cogs"></i> Gerenciar Cursos
                </a>
                <a class="nav-link" href="reports.php">
                    <i class="fas fa-chart-bar"></i> Relatórios
                </a>
            <?php endif; ?>
            
            <hr class="text-white">
            <a class="nav-link" href="profile.php">
                <i class="fas fa-user-edit"></i> Meu Perfil
            </a>
            <a class="nav-link" href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </nav>
    </div>
    
    <!-- Mobile menu button -->
    <button class="btn btn-dark d-md-none" id="sidebarToggle" style="position: fixed; top: 10px; left: 10px; z-index: 1001;">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Main content -->
    <div class="main-content">
    <?php endif; ?>