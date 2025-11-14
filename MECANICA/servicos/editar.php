<?php
require_once '../conexao.php';

// Verificar se o ID foi passado
if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$id_servico = $_GET['id'];

// Buscar dados do serviço
$sql = "SELECT * FROM SERVICO WHERE ID_SERVICO = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_servico]);
$servico = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se serviço existe
if (!$servico) {
    header("Location: listar.php");
    exit;
}

// Processar formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_servico = substr($_POST['nome_servico'], 0, 100);
    $mao_de_obra = str_replace(',', '.', $_POST['mao_de_obra']);
    $tempo = $_POST['tempo'];
    $descricao_servico = substr($_POST['descricao_servico'], 0, 500);
    
    try {
        $sql = "UPDATE SERVICO SET 
                NOME_SERVICO = ?, 
                MAO_DE_OBRA = ?, 
                TEMPO = ?, 
                DESCRICAO_SERVICO = ? 
                WHERE ID_SERVICO = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome_servico, $mao_de_obra, $tempo, $descricao_servico, $id_servico]);
        
        header("Location: listar.php");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao atualizar serviço: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Serviço - Oficina</title>
    <link rel="stylesheet" href="formulario.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="header">
                <h1>✏️ Editar Serviço</h1>
                <p>Editando dados do serviço</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert error">
                    <?= $erro ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro">
                <div class="form-group">
                    <label for="nome_servico">Nome do Serviço *</label>
                    <input type="text" id="nome_servico" name="nome_servico" value="<?= htmlspecialchars($servico['NOME_SERVICO']) ?>" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label for="mao_de_obra">Preço Mão de Obra (R$) *</label>
                    <input type="text" id="mao_de_obra" name="mao_de_obra" value="<?= number_format($servico['MAO_DE_OBRA'], 2, ',', '.') ?>" required>
                </div>

                <div class="form-group">
                    <label for="tempo">Tempo Estimado</label>
                    <input type="time" id="tempo" name="tempo" value="<?= $servico['TEMPO'] ?>" step="1">
                </div>

                <div class="form-group">
                    <label for="descricao_servico">Descrição</label>
                    <textarea id="descricao_servico" name="descricao_servico" rows="4" maxlength="500"><?= htmlspecialchars($servico['DESCRICAO_SERVICO']) ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Atualizar Serviço</button>
                    <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>