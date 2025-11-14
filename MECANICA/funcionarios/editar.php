<?php
require_once '../conexao.php';

// Verificar se o ID foi passado
if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$id_funcionario = $_GET['id'];

// Buscar dados do funcionário
$sql = "SELECT * FROM FUNCIONARIO WHERE ID_FUNCIONARIO = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_funcionario]);
$funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se funcionário existe
if (!$funcionario) {
    header("Location: listar.php");
    exit;
}

// Processar formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = substr($_POST['nome'], 0, 100);
    $cpf = substr($_POST['cpf'], 0, 14);
    $telefone = substr($_POST['telefone'], 0, 15);
    
    try {
        $sql = "UPDATE FUNCIONARIO SET 
                NOME_FUNCIONARIO = ?, 
                CPF_FUNCIONARIO = ?, 
                TELEFONE_FUNCIONARIO = ? 
                WHERE ID_FUNCIONARIO = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $cpf, $telefone, $id_funcionario]);
        
        header("Location: listar.php");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao atualizar funcionário: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Funcionário - Oficina</title>
    <link rel="stylesheet" href="formulario.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="header">
                <h1>✏️ Editar Funcionário</h1>
                <p>Editando dados do funcionário</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert error">
                    <?= $erro ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro">
                <div class="form-group">
                    <label for="nome">Nome Completo *</label>
                    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($funcionario['NOME_FUNCIONARIO']) ?>" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label for="cpf">CPF *</label>
                    <input type="text" id="cpf" name="cpf" value="<?= htmlspecialchars($funcionario['CPF_FUNCIONARIO']) ?>" maxlength="14" required>
                </div>

                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($funcionario['TELEFONE_FUNCIONARIO']) ?>" maxlength="15">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Atualizar Funcionário</button>
                    <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>