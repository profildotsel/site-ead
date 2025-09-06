<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'includes/auth.php';

requireRole('administrador');

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Processar ações
if ($_POST) {
    if (isset($_POST['delete_user'])) {
        $user->id = $_POST['user_id'];
        if ($user->delete()) {
            $success_message = "Usuário excluído com sucesso!";
        } else {
            $error_message = "Erro ao excluir usuário.";
        }
    } elseif (isset($_POST['update_role'])) {
        $user->id = $_POST['user_id'];
        $user->readOne();
        $user->role = $_POST['new_role'];
        if ($user->update()) {
            $success_message = "Papel do usuário atualizado com sucesso!";
        } else {
            $error_message = "Erro ao atualizar papel do usuário.";
        }
    }
}

$users = $user->readAll();

$page_title = "Gerenciar Usuários - Plataforma EAD";
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <i class="fas fa-users-cog"></i> Gerenciar Usuários
                <a href="register.php" class="btn btn-primary float-end">
                    <i class="fas fa-user-plus"></i> Criar Usuário
                </a>
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
                    <h5><i class="fas fa-users"></i> Lista de Usuários</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Usuário</th>
                                    <th>Papel</th>
                                    <th>Data Criação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $users->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $row['role'] === 'administrador' ? 'danger' : 
                                                ($row['role'] === 'instrutor' ? 'warning' : 'primary'); 
                                        ?>">
                                            <?php echo ucfirst($row['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <?php if ($row['id'] != getCurrentUserId()): ?>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-warning" 
                                                        onclick="changeRole(<?php echo $row['id']; ?>, '<?php echo $row['role']; ?>', '<?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>')">
                                                    <i class="fas fa-user-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-info">Você</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para alterar papel -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-edit"></i> Alterar Papel do Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="roleForm">
                <div class="modal-body">
                    <p>Alterar papel do usuário <strong id="userName"></strong>:</p>
                    <input type="hidden" name="user_id" id="userIdToUpdate">
                    <div class="mb-3">
                        <label for="new_role" class="form-label">Novo Papel:</label>
                        <select class="form-control" name="new_role" id="new_role" required>
                            <option value="estudante">Estudante</option>
                            <option value="instrutor">Instrutor</option>
                            <option value="administrador">Administrador</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="update_role" class="btn btn-warning">
                        <i class="fas fa-save"></i> Alterar Papel
                    </button>
                </div>
            </form>
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
                <p>Tem certeza que deseja excluir o usuário <strong id="userNameToDelete"></strong>?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita e excluirá todos os dados relacionados ao usuário.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" class="d-inline" id="deleteForm">
                    <input type="hidden" name="user_id" id="userIdToDelete">
                    <button type="submit" name="delete_user" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function changeRole(userId, currentRole, userName) {
    document.getElementById('userIdToUpdate').value = userId;
    document.getElementById('userName').textContent = userName;
    document.getElementById('new_role').value = currentRole;
    new bootstrap.Modal(document.getElementById('roleModal')).show();
}

function confirmDelete(userId, userName) {
    document.getElementById('userIdToDelete').value = userId;
    document.getElementById('userNameToDelete').textContent = userName;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>