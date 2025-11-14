<?php
// conexao.php simples para teste
require_once '../conexao.php';

// Debug - remover depois
echo "<!-- Debug: Arquivo excluir.php carregado -->";

$id_veiculo = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_veiculo == 0) {
    header("Location: listar.php");
    exit;
}

// Buscar dados do veículo
$sql = "SELECT v.*, c.NOME_CLIENTE 
        FROM VEICULO v 
        JOIN CLIENTE c ON v.ID_CLIENTE = c.ID_CLIENTE 
        WHERE v.ID_VEICULO = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_veiculo]);
$veiculo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$veiculo) {
    echo "Veículo não encontrado!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Verificar se tem OS vinculadas
        $sql_os = "SELECT COUNT(*) as total FROM OS WHERE ID_VEICULO = ?";
        $stmt_os = $pdo->prepare($sql_os);
        $stmt_os->execute([$id_veiculo]);
        $os_count = $stmt_os->fetch(PDO::FETCH_ASSOC);
        
        if ($os_count['total'] > 0) {
            $erro = "Não é possível excluir: existem ordens de serviço vinculadas.";
        } else {
            $sql_delete = "DELETE FROM VEICULO WHERE ID_VEICULO = ?";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->execute([$id_veiculo]);
            
            header("Location: listar.php?sucesso=1");
            exit;
        }
    } catch (Exception $e) {
        $erro = "Erro ao excluir: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Excluir Veículo</title>
    <link rel="stylesheet" href="formulario.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="header">
                <h1>🗑️ Excluir Veículo</h1>
                <p>ID: <?= $id_veiculo ?></p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert error"><?= $erro ?></div>
                <div class="form-actions">
                    <a href="listar.php" class="btn btn-secondary">Voltar</a>
                </div>
            <?php else: ?>
                <div class="alert error">
                    <strong>Confirma exclusão?</strong><br><br>
                    <strong><?= htmlspecialchars($veiculo['MARCA'] . ' ' . $veiculo['MODELO']) ?></strong><br>
                    Placa: <?= htmlspecialchars($veiculo['PLACA']) ?><br>
                    Cliente: <?= htmlspecialchars($veiculo['NOME_CLIENTE']) ?>
                </div>

                <form method="POST">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                        <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>