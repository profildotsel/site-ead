<?php
ob_start();

require_once 'config/database.php';
require_once 'classes/Course.php';
require_once 'classes/User.php';
require_once 'classes/Lesson.php';
require_once 'includes/auth.php';

requireRole('administrador');

$database = new Database();
$db = $database->getConnection();
$course = new Course($db);
$user = new User($db);
$lesson = new Lesson($db);

// Bloco para carregar aulas via AJAX (GET request)
if (isset($_GET['get_lessons']) && isset($_GET['course_id'])) {
    ob_clean();
    error_reporting(0);
    ini_set('display_errors', 0);
    header('Content-Type: application/json');
    $lesson->course_id = $_GET['course_id'];
    $lessons_stmt = $lesson->readByCourse();
    $lessons_arr = [];
    if ($lessons_stmt) {
        while ($row = $lessons_stmt->fetch(PDO::FETCH_ASSOC)) {
            $lessons_arr[] = $row;
        }
    }
    echo json_encode($lessons_arr);
    exit();
}

// Processar ações do formulário (POST request)
if ($_POST) {
    if (isset($_POST['delete_course'])) {
        $course->id = $_POST['course_id'];
        if ($course->delete()) { $success_message = "Curso excluído com sucesso!"; } else { $error_message = "Erro ao excluir curso."; }
    } elseif (isset($_POST['update_status'])) {
        $course->id = $_POST['course_id'];
        $course->readOne();
        $course->status = $_POST['new_status'];
        if ($course->update()) { $success_message = "Status do curso atualizado com sucesso!"; } else { $error_message = "Erro ao atualizar status do curso."; }
    } elseif (isset($_POST['edit_course'])) {
        $course->id = $_POST['course_id'];
        $course->title = $_POST['title'];
        $course->description = $_POST['description'];
        $course->instructor_id = $_POST['instructor_id'];
        $course->status = $_POST['status'];
        $course->start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $course->end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        if ($course->update()) { $success_message = "Curso atualizado com sucesso!"; } else { $error_message = "Erro ao atualizar curso."; }
    } elseif (isset($_POST['add_lesson'])) {
        $lesson->course_id = $_POST['course_id'];
        $lesson->title = $_POST['lesson_title'];
        $lesson->content = $_POST['lesson_content'];
        // Garante que video_url seja string vazia se não for fornecido
        $lesson->video_url = !empty($_POST['video_url']) ? $_POST['video_url'] : ''; 
        $lesson->order_number = $_POST['order_number'];
        if ($lesson->create()) { $success_message = "Aula adicionada com sucesso!"; } else { $error_message = "Erro ao adicionar aula."; }
    } elseif (isset($_POST['edit_lesson'])) {
        $lesson->id = $_POST['lesson_id'];
        $lesson->title = $_POST['lesson_title'];
        $lesson->content = $_POST['lesson_content'];
        // Garante que video_url seja string vazia se não for fornecido
        $lesson->video_url = !empty($_POST['video_url']) ? $_POST['video_url'] : ''; 
        $lesson->order_number = $_POST['order_number'];
        if ($lesson->update()) { $success_message = "Aula atualizada com sucesso!"; } else { $error_message = "Erro ao atualizar aula."; }
    } elseif (isset($_POST['delete_lesson'])) {
        $lesson->id = $_POST['lesson_id'];
        if ($lesson->delete()) { $success_message = "Aula excluída com sucesso!"; } else { $error_message = "Erro ao excluir aula."; }
    }
}

$courses = $course->readAll();
$page_title = "Gerenciar Cursos - Plataforma EAD";
include 'includes/header.php';

$instructors = $db->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE role = 'instrutor' ORDER BY first_name");
$instructors_list = $instructors->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h1 class="h3 mb-4">
        <i class="fas fa-cogs"></i> Gerenciar Cursos
        <a href="create_course.php" class="btn btn-primary float-end"><i class="fas fa-plus"></i> Criar Curso</a>
    </h1>

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

    <div class="card">
        <div class="card-header"><h5><i class="fas fa-list"></i> Lista de Cursos</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead><tr><th>ID</th><th>Título</th><th>Instrutor</th><th>Status</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php while ($row = $courses->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><span class="badge bg-<?php echo $row['status'] === 'publicado' ? 'success' : 'warning text-dark'; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="view_course.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" title="Visualizar"><i class="fas fa-eye"></i></a>
                                    <button type="button" class="btn btn-sm btn-success" title="Editar Curso" onclick="editCourse(<?php echo htmlspecialchars(json_encode($row)); ?>)"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-info" title="Gerenciar Aulas" onclick="manageLessons(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['title'])); ?>')"><i class="fas fa-play-circle"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger" title="Excluir" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['title'])); ?>')"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editCourseModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Editar Curso</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST"><div class="modal-body"><input type="hidden" name="course_id" id="editCourseId"><div class="mb-3"><label for="edit_title" class="form-label">Título</label><input type="text" class="form-control" name="title" id="edit_title" required></div><div class="mb-3"><label for="edit_description" class="form-label">Descrição</label><textarea name="description" id="edit_description"></textarea></div><div class="mb-3"><label for="edit_instructor_id" class="form-label">Instrutor</label><select class="form-control" name="instructor_id" id="edit_instructor_id" required><?php foreach ($instructors_list as $instructor):?><option value="<?php echo $instructor['id'];?>"><?php echo htmlspecialchars($instructor['name']);?></option><?php endforeach;?></select></div><div class="row"><div class="col-md-4 mb-3"><label for="edit_status" class="form-label">Status</label><select class="form-control" name="status" id="edit_status" required><option value="rascunho">Rascunho</option><option value="publicado">Publicado</option></select></div><div class="col-md-4 mb-3"><label for="edit_start_date" class="form-label">Data de Início</label><input type="date" class="form-control" name="start_date" id="edit_start_date"></div><div class="col-md-4 mb-3"><label for="edit_end_date" class="form-label">Data de Término</label><input type="date" class="form-control" name="end_date" id="edit_end_date"></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" name="edit_course" class="btn btn-success">Salvar</button></div></form></div></div></div>
<div class="modal fade" id="lessonsModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Gerenciar Aulas - <span id="courseNameForLessons"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row"><div class="col-md-5 border-end"><h6 class="mb-3">Adicionar Nova Aula</h6><form method="POST"><input type="hidden" name="course_id" id="lessonCourseId"><div class="mb-3"><label class="form-label">Título</label><input type="text" class="form-control" name="lesson_title" required></div><div class="mb-3"><label class="form-label">Ordem</label><input type="number" class="form-control" name="order_number" min="1" required></div><div class="mb-3"><label class="form-label">URL Vídeo</label><input type="url" class="form-control" name="video_url" placeholder="Opcional"></div><div class="mb-3"><label class="form-label">Conteúdo</label><textarea name="lesson_content" id="lesson_content"></textarea></div><button type="submit" name="add_lesson" class="btn btn-primary w-100">Adicionar</button></form></div><div class="col-md-7"><h6 class="mb-3">Aulas do Curso</h6><div id="lessonsList"><div class="table-responsive"><table class="table table-sm table-hover"><thead><tr><th>ID</th><th>Ordem</th><th>Título</th><th>Ações</th></tr></thead><tbody id="lessonsTableBody"></tbody></table></div></div></div></div></div></div></div></div>
<div class="modal fade" id="editLessonModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Editar Aula</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST"><div class="modal-body"><input type="hidden" name="lesson_id" id="editLessonId"><div class="mb-3"><label class="form-label">Título</label><input type="text" class="form-control" name="lesson_title" id="edit_lesson_title" required></div><div class="mb-3"><label class="form-label">Ordem</label><input type="number" class="form-control" name="order_number" id="edit_lesson_order" min="1" required></div><div class="mb-3"><label class="form-label">URL Vídeo</label><input type="url" class="form-control" name="video_url" id="edit_lesson_video_url" placeholder="Opcional"></div><div class="mb-3"><label class="form-label">Conteúdo</label><textarea name="lesson_content" id="edit_lesson_content"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" name="edit_lesson" class="btn btn-success">Salvar</button></div></form></div></div></div>
<div class="modal fade" id="deleteModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title text-danger">Confirmar Exclusão</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p>Tem certeza que deseja excluir o curso <strong id="courseTitleToDelete"></strong>?</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><form method="POST"><input type="hidden" name="course_id" id="courseIdToDelete"><button type="submit" name="delete_course" class="btn btn-danger">Excluir</button></form></div></div></div></div>

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
let currentLessonsData = [];
let editCourseEditor, addLessonEditor, editLessonEditor;

function editCourse(courseData) {
    document.getElementById('editCourseId').value = courseData.id;
    document.getElementById('edit_title').value = courseData.title;
    document.getElementById('edit_instructor_id').value = courseData.instructor_id;
    document.getElementById('edit_status').value = courseData.status;
    document.getElementById('edit_start_date').value = courseData.start_date;
    document.getElementById('edit_end_date').value = courseData.end_date;

    if (CKEDITOR.instances['edit_description']) CKEDITOR.instances['edit_description'].destroy(true);
    editCourseEditor = CKEDITOR.replace('edit_description');
    editCourseEditor.setData(courseData.description || '');
    
    new bootstrap.Modal(document.getElementById('editCourseModal')).show();
}

function manageLessons(courseId, courseTitle) {
    document.getElementById('lessonCourseId').value = courseId;
    document.getElementById('courseNameForLessons').textContent = courseTitle;
    const tableBody = document.getElementById('lessonsTableBody');
    tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Carregando...</td></tr>';
    
    if (CKEDITOR.instances['lesson_content']) CKEDITOR.instances['lesson_content'].destroy(true);
    addLessonEditor = CKEDITOR.replace('lesson_content');
    addLessonEditor.setData('');

    fetch(`manage_courses.php?get_lessons=1&course_id=${courseId}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            currentLessonsData = data;
            tableBody.innerHTML = '';
            if (data && data.length > 0) {
                data.sort((a, b) => (a.order_number || 0) - (b.order_number || 0)).forEach(lesson => {
                    const row = `<tr>
                        <td>${lesson.id}</td>
                        <td>${lesson.order_number || '-'}</td> <td>${lesson.title}</td>
                        <td>
                            <button class="btn btn-sm btn-success" onclick="openEditLessonModal(${lesson.id})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="deleteLesson(${lesson.id}, '${addslashes(lesson.title)}')"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>`;
                    tableBody.innerHTML += row;
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Nenhuma aula encontrada.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error fetching lessons:', error);
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Erro ao carregar aulas.</td></tr>';
        });
    
    new bootstrap.Modal(document.getElementById('lessonsModal')).show();
}

function openEditLessonModal(lessonId) {
    const lesson = currentLessonsData.find(l => l.id == lessonId);
    if (!lesson) {
        console.error('Aula não encontrada para o ID:', lessonId);
        return;
    }

    document.getElementById('editLessonId').value = lesson.id;
    document.getElementById('edit_lesson_title').value = lesson.title;
    document.getElementById('edit_lesson_order').value = lesson.order_number || ''; 
    document.getElementById('edit_lesson_video_url').value = lesson.video_url || ''; // Garante string vazia
    
    if (CKEDITOR.instances['edit_lesson_content']) CKEDITOR.instances['edit_lesson_content'].destroy(true);
    editLessonEditor = CKEDITOR.replace('edit_lesson_content');
    editLessonEditor.setData(lesson.content || '');
    
    new bootstrap.Modal(document.getElementById('editLessonModal')).show();
}

function deleteLesson(lessonId, lessonTitle) {
    if (confirm(`Excluir a aula "${lessonTitle}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="lesson_id" value="${lessonId}"><input type="hidden" name="delete_lesson" value="1">`;
        document.body.appendChild(form);
        form.submit();
    }
}

function confirmDelete(courseId, courseTitle) {
    document.getElementById('courseIdToDelete').value = courseId;
    document.getElementById('courseTitleToDelete').textContent = courseTitle;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function addslashes(str) {
    return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}

// Destruição dos editores ao fechar modais
$(document).ready(function() {
    $('#editCourseModal').on('hidden.bs.modal', function () {
        if (CKEDITOR.instances['edit_description']) CKEDITOR.instances['edit_description'].destroy(true);
        editCourseEditor = null;
    });
    $('#lessonsModal').on('hidden.bs.modal', function () {
        if (CKEDITOR.instances['lesson_content']) CKEDITOR.instances['lesson_content'].destroy(true);
        addLessonEditor = null;
    });
    $('#editLessonModal').on('hidden.bs.modal', function () {
        if (CKEDITOR.instances['edit_lesson_content']) CKEDITOR.instances['edit_lesson_content'].destroy(true);
        editLessonEditor = null;
    });
});
</script>

<?php 
include 'includes/footer.php'; 
ob_end_flush();
?>