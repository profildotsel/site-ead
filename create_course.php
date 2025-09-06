<?php
require_once 'config/database.php';
require_once 'classes/Course.php';
require_once 'includes/auth.php';

requireRoles(['administrador', 'instrutor']);

$database = new Database();
$db = $database->getConnection();
$course = new Course($db);

$message = '';
$message_type = '';

if ($_POST) {
    $course->title = $_POST['title'] ?? '';
    $course->description = $_POST['description'] ?? '';
    $course->instructor_id = getCurrentUserId();
    $course->status = $_POST['status'] ?? 'rascunho';
    $course->start_date = $_POST['start_date'] ?? null;
    $course->end_date = $_POST['end_date'] ?? null;
    
    if (empty($course->title)) {
        $message = 'O título do curso é obrigatório.';
        $message_type = 'danger';
    } elseif (empty($course->description)) {
        $message = 'A descrição do curso é obrigatória.';
        $message_type = 'danger';
    } else {
        if ($course->create()) {
            $message = 'Curso criado com sucesso!';
            $message_type = 'success';
            // Limpar campos após sucesso
            $_POST = array();
        } else {
            $message = 'Erro ao criar curso. Tente novamente.';
            $message_type = 'danger';
        }
    }
}

$page_title = "Criar Curso - Plataforma EAD";
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <i class="fas fa-plus"></i> Criar Novo Curso
                <a href="my_courses.php" class="btn btn-secondary float-end">
                    <i class="fas fa-arrow-left"></i> Voltar para Meus Cursos
                </a>
            </h1>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-book"></i> Informações do Curso</h5>
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
                        <div class="mb-3">
                            <label for="title" class="form-label">
                                <i class="fas fa-heading"></i> Título do Curso *
                            </label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                                   placeholder="Ex: Introdução ao Desenvolvimento Web" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left"></i> Descrição do Curso *
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="5" 
                                      placeholder="Descreva o conteúdo e objetivos do curso..." required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">
                                    <i class="fas fa-calendar"></i> Data de Início
                                </label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?php echo $_POST['start_date'] ?? ''; ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">
                                    <i class="fas fa-calendar-check"></i> Data de Término
                                </label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="<?php echo $_POST['end_date'] ?? ''; ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="status" class="form-label">
                                <i class="fas fa-flag"></i> Status do Curso
                            </label>
                            <select class="form-control" id="status" name="status">
                                <option value="rascunho" <?php echo ($_POST['status'] ?? 'rascunho') === 'rascunho' ? 'selected' : ''; ?>>
                                    Rascunho (não visível para alunos)
                                </option>
                                <option value="publicado" <?php echo ($_POST['status'] ?? '') === 'publicado' ? 'selected' : ''; ?>>
                                    Publicado (visível para alunos)
                                </option>
                            </select>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> 
                                Cursos em rascunho não aparecem na lista de cursos disponíveis para inscrição.
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="my_courses.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Criar Curso
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Dicas para criação de curso -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6><i class="fas fa-lightbulb"></i> Dicas para criar um bom curso</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li><strong>Título claro:</strong> Use um título descritivo e que chame atenção.</li>
                        <li><strong>Descrição completa:</strong> Explique o que o aluno vai aprender e quais são os pré-requisitos.</li>
                        <li><strong>Planejamento:</strong> Defina datas realistas para início e término do curso.</li>
                        <li><strong>Conteúdo estruturado:</strong> Após criar o curso, organize as aulas em uma sequência lógica.</li>
                        <li><strong>Status rascunho:</strong> Mantenha como rascunho até ter pelo menos algumas aulas prontas.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>