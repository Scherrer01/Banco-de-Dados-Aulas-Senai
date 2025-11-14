<?php
require_once '../conexao.php';

// Verificar se o ID foi passado
if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$id_cliente = $_GET['id'];

// Buscar dados do cliente
$sql = "SELECT * FROM CLIENTE WHERE ID_CLIENTE = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_cliente]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se cliente existe
if (!$cliente) {
    header("Location: listar.php");
    exit;
}

// Processar formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $endereco = $_POST['endereco'];
    
    try {
        $sql = "UPDATE CLIENTE SET 
                NOME_CLIENTE = ?, 
                CPF_CLIENTE = ?, 
                TELEFONE_CLIENTE = ?, 
                EMAIL = ?, 
                ENDERECO = ? 
                WHERE ID_CLIENTE = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $cpf, $telefone, $email, $endereco, $id_cliente]);
        
        header("Location: listar.php");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao atualizar cliente: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente - Oficina</title>
    <link rel="stylesheet" href="editar.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="header">
                <h1>✏️ Editar Cliente</h1>
                <p>Editando dados do cliente</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert error">
                    <?= $erro ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro">
                <div class="form-group">
                    <label for="nome">Nome Completo *</label>
                    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($cliente['NOME_CLIENTE']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="cpf">CPF *</label>
                    <input type="text" id="cpf" name="cpf" value="<?= htmlspecialchars($cliente['CPF_CLIENTE']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($cliente['TELEFONE_CLIENTE']) ?>">
                </div>

                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($cliente['EMAIL']) ?>">
                </div>

                <div class="form-group">
                    <label for="endereco">Endereço</label>
                    <textarea id="endereco" name="endereco" rows="3"><?= htmlspecialchars($cliente['ENDERECO']) ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Atualizar Cliente</button>
                    <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>