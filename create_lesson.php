<?php
require_once 'config/database.php';
require_once 'classes/Course.php';
require_once 'classes/Lesson.php';
require_once 'includes/auth.php';

requireRoles(['administrador', 'instrutor']);

$database = new Database();
$db = $database->getConnection();
$course = new Course($db);
$lesson = new Lesson($db);

$course_id = $_GET['course_id'] ?? 0;
$course->id = $course_id;
$course->readOne();

// Verificar se o instrutor pode editar este curso
if (isInstructor() && $course->instructor_id != getCurrentUserId()) {
    header("Location: access_denied.php");
    exit();
}

$message = '';
$message_type = '';

if ($_POST) {
    $lesson->course_id = $course_id;
    $lesson->title = $_POST['title'] ?? '';
    $lesson->content = $_POST['content'] ?? '';
    $lesson->order = $_POST['order'] ?? $lesson->getNextOrder($course_id);
    
    if (empty($lesson->title)) {
        $message = 'O título da aula é obrigatório.';
        $message_type = 'danger';
    } elseif (empty($lesson->content)) {
        $message = 'O conteúdo da aula é obrigatório.';
        $message_type = 'danger';
    } else {
        if ($lesson->create()) {
            header("Location: course_lessons.php?id=" . $course_id);
            exit();
        } else {
            $message = 'Erro ao criar aula. Tente novamente.';
            $message_type = 'danger';
        }
    }
}

$next_order = $lesson->getNextOrder($course_id);

$page_title = "Criar Aula - " . htmlspecialchars($course->title);
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <i class="fas fa-plus"></i> Criar Nova Aula
                <small class="text-muted">- <?php echo htmlspecialchars($course->title); ?></small>
                <a href="course_lessons.php?id=<?php echo $course_id; ?>" class="btn btn-secondary float-end">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </h1>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-play-circle"></i> Informações da Aula</h5>
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
                            <div class="col-md-8 mb-3">
                                <label for="title" class="form-label">
                                    <i class="fas fa-heading"></i> Título da Aula *
                                </label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                                       placeholder="Ex: Introdução ao HTML" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="order" class="form-label">
                                    <i class="fas fa-sort-numeric-up"></i> Ordem da Aula
                                </label>
                                <input type="number" class="form-control" id="order" name="order" 
                                       value="<?php echo $_POST['order'] ?? $next_order; ?>" min="1" required>
                                <div class="form-text">Ordem em que a aula aparecerá no curso</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="content" class="form-label">
                                <i class="fas fa-align-left"></i> Conteúdo da Aula *
                            </label>
                            <textarea class="form-control" id="content" name="content" rows="15" 
                                      placeholder="Digite o conteúdo da aula aqui..." required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> 
                                Você pode usar HTML para formatar o conteúdo (ex: &lt;h2&gt;, &lt;p&gt;, &lt;strong&gt;, &lt;em&gt;, etc.)
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="course_lessons.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Criar Aula
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Preview do conteúdo -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6><i class="fas fa-eye"></i> Preview do Conteúdo</h6>
                </div>
                <div class="card-body" id="contentPreview">
                    <p class="text-muted">O preview do conteúdo aparecerá aqui conforme você digita...</p>
                </div>
            </div>

            <!-- Dicas de formatação -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6><i class="fas fa-lightbulb"></i> Dicas de Formatação HTML</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Títulos:</h6>
                            <code>&lt;h2&gt;Título Principal&lt;/h2&gt;</code><br>
                            <code>&lt;h3&gt;Subtítulo&lt;/h3&gt;</code>
                            
                            <h6 class="mt-3">Parágrafos:</h6>
                            <code>&lt;p&gt;Seu texto aqui&lt;/p&gt;</code>
                            
                            <h6 class="mt-3">Formatação:</h6>
                            <code>&lt;strong&gt;Negrito&lt;/strong&gt;</code><br>
                            <code>&lt;em&gt;Itálico&lt;/em&gt;</code>
                        </div>
                        <div class="col-md-6">
                            <h6>Listas:</h6>
                            <code>&lt;ul&gt;&lt;li&gt;Item 1&lt;/li&gt;&lt;li&gt;Item 2&lt;/li&gt;&lt;/ul&gt;</code>
                            
                            <h6 class="mt-3">Links:</h6>
                            <code>&lt;a href="url"&gt;Texto do link&lt;/a&gt;</code>
                            
                            <h6 class="mt-3">Quebra de linha:</h6>
                            <code>&lt;br&gt;</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview em tempo real do conteúdo
document.getElementById('content').addEventListener('input', function() {
    const content = this.value;
    const preview = document.getElementById('contentPreview');
    
    if (content.trim() === '') {
        preview.innerHTML = '<p class="text-muted">O preview do conteúdo aparecerá aqui conforme você digita...</p>';
    } else {
        preview.innerHTML = content;
    }
});
</script>

<?php include 'includes/footer.php'; ?>