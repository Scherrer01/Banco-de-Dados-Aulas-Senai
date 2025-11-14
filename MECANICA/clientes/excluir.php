<?php
require_once '../conexao.php';

// Verificar se o ID foi passado
if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$id_cliente = $_GET['id'];

// Buscar dados do cliente para confirmar
$sql = "SELECT * FROM CLIENTE WHERE ID_CLIENTE = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_cliente]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se cliente existe
if (!$cliente) {
    header("Location: listar.php");
    exit;
}

// Processar exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verificar se o cliente tem veículos antes de excluir
        $sql_veiculos = "SELECT COUNT(*) as total FROM VEICULO WHERE ID_CLIENTE = ?";
        $stmt_veiculos = $pdo->prepare($sql_veiculos);
        $stmt_veiculos->execute([$id_cliente]);
        $veiculos = $stmt_veiculos->fetch(PDO::FETCH_ASSOC);
        
        if ($veiculos['total'] > 0) {
            $erro = "Não é possível excluir este cliente pois existem veículos vinculados a ele.";
        } else {
            $sql = "DELETE FROM CLIENTE WHERE ID_CLIENTE = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_cliente]);
            
            header("Location: listar.php");
            exit;
        }
    } catch (PDOException $e) {
        $erro = "Erro ao excluir cliente: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Cliente - Oficina</title>
    <link rel="stylesheet" href="cadastrar.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="header">
                <h1>🗑️ Excluir Cliente</h1>
                <p>Confirmação de exclusão</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert error">
                    <?= $erro ?>
                </div>
                <div class="form-actions">
                    <a href="listar.php" class="btn btn-secondary">Voltar</a>
                </div>
            <?php else: ?>
                <div class="alert error">
                    <strong>Atenção!</strong> Você está prestes a excluir o cliente:<br>
                    <strong><?= htmlspecialchars($cliente['NOME_CLIENTE']) ?></strong> - CPF: <?= htmlspecialchars($cliente['CPF_CLIENTE']) ?>
                </div>

                <p style="text-align: center; color: #7f8c8d; margin-bottom: 20px;">
                    Esta ação não pode ser desfeita. Tem certeza que deseja continuar?
                </p>

                <form method="POST" class="form-cadastro">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-danger">Sim, Excluir Cliente</button>
                        <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .btn-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #c0392b, #e74c3c);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }
    </style>
</body>
</html>