<?php
require_once '../conexao.php';

// Verificar se o ID foi passado
if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$id_peca = $_GET['id'];

// Buscar dados da peça
$sql = "SELECT * FROM PECAS WHERE ID_PECA = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_peca]);
$peca = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se peça existe
if (!$peca) {
    header("Location: listar.php");
    exit;
}

// Processar formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_peca = substr($_POST['nome_peca'], 0, 100);
    $preco = str_replace(',', '.', $_POST['preco']);
    $quantidade = intval($_POST['quantidade']);
    $descricao_peca = substr($_POST['descricao_peca'], 0, 500);
    
    try {
        $sql = "UPDATE PECAS SET 
                NOME_PECA = ?, 
                PRECO = ?, 
                QUANTIDADE = ?, 
                DESCRICAO_PECA = ? 
                WHERE ID_PECA = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome_peca, $preco, $quantidade, $descricao_peca, $id_peca]);
        
        header("Location: listar.php");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao atualizar peça: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Peça - Oficina</title>
    <link rel="stylesheet" href="formulario.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="header">
                <h1>✏️ Editar Peça</h1>
                <p>Editando dados da peça</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert error">
                    <?= $erro ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro">
                <div class="form-group">
                    <label for="nome_peca">Nome da Peça *</label>
                    <input type="text" id="nome_peca" name="nome_peca" value="<?= htmlspecialchars($peca['NOME_PECA']) ?>" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label for="preco">Preço (R$) *</label>
                    <input type="text" id="preco" name="preco" value="<?= number_format($peca['PRECO'], 2, ',', '.') ?>" required>
                </div>

                <div class="form-group">
                    <label for="quantidade">Quantidade em Estoque *</label>
                    <input type="number" id="quantidade" name="quantidade" value="<?= $peca['QUANTIDADE'] ?>" min="0" required>
                </div>

                <div class="form-group">
                    <label for="descricao_peca">Descrição</label>
                    <textarea id="descricao_peca" name="descricao_peca" rows="4" maxlength="500"><?= htmlspecialchars($peca['DESCRICAO_PECA']) ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Atualizar Peça</button>
                    <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>