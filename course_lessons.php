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

$course_id = $_GET['id'] ?? 0;
$course->id = $course_id;
$course->readOne();

// Verificar se o instrutor pode editar este curso
if (isInstructor() && $course->instructor_id != getCurrentUserId()) {
    header("Location: access_denied.php");
    exit();
}

$lessons = $lesson->readByCourse($course_id);

// Processar exclusão de aula
if ($_POST && isset($_POST['delete_lesson'])) {
    $lesson->id = $_POST['lesson_id'];
    if ($lesson->delete()) {
        $success_message = "Aula excluída com sucesso!";
        $lessons = $lesson->readByCourse($course_id); // Recarregar lista
    } else {
        $error_message = "Erro ao excluir aula.";
    }
}

$page_title = "Gerenciar Aulas - " . htmlspecialchars($course->title);
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <i class="fas fa-list"></i> Gerenciar Aulas
                <small class="text-muted">- <?php echo htmlspecialchars($course->title); ?></small>
                <div class="float-end">
                    <a href="create_lesson.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nova Aula
                    </a>
                    <a href="view_course.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">
                        <i class="fas fa-eye"></i> Ver Curso
                    </a>
                </div>
            </h1>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list"></i> Lista de Aulas</h5>
                </div>
                <div class="card-body">
                    <?php if ($lessons->rowCount() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Ordem</th>
                                        <th>Título</th>
                                        <th>Data Criação</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $lessons->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $row['order']; ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="view_lesson.php?id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-primary" title="Visualizar">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit_lesson.php?id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        title="Excluir" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['title']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-list fa-4x text-muted mb-3"></i>
                            <h4>Nenhuma aula criada</h4>
                            <p class="text-muted">Comece criando a primeira aula do seu curso.</p>
                            <a href="create_lesson.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Criar Primeira Aula
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmação para exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-danger"></i> Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a aula <strong id="lessonTitle"></strong>?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" class="d-inline" id="deleteForm">
                    <input type="hidden" name="lesson_id" id="lessonIdToDelete">
                    <button type="submit" name="delete_lesson" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(lessonId, lessonTitle) {
    document.getElementById('lessonIdToDelete').value = lessonId;
    document.getElementById('lessonTitle').textContent = lessonTitle;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>