<?php
require_once '../conexao.php';

// Verificar se o ID foi passado
if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$id_servico = $_GET['id'];

// Buscar dados do serviço para confirmar
$sql = "SELECT * FROM SERVICO WHERE ID_SERVICO = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_servico]);
$servico = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se serviço existe
if (!$servico) {
    header("Location: listar.php");
    exit;
}

// Processar exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verificar se o serviço tem OS antes de excluir
        $sql_os = "SELECT COUNT(*) as total FROM REALIZA WHERE ID_SERVICO = ?";
        $stmt_os = $pdo->prepare($sql_os);
        $stmt_os->execute([$id_servico]);
        $os_count = $stmt_os->fetch(PDO::FETCH_ASSOC);
        
        if ($os_count['total'] > 0) {
            $erro = "Não é possível excluir este serviço pois existem ordens de serviço vinculadas a ele.";
        } else {
            $sql = "DELETE FROM SERVICO WHERE ID_SERVICO = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_servico]);
            
            header("Location: listar.php");
            exit;
        }
    } catch (PDOException $e) {
        $erro = "Erro ao excluir serviço: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Serviço - Oficina</title>
    <link rel="stylesheet" href="formulario.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="header">
                <h1>🗑️ Excluir Serviço</h1>
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
                    <strong>Atenção!</strong> Você está prestes a excluir o serviço:<br>
                    <strong><?= htmlspecialchars($servico['NOME_SERVICO']) ?></strong><br>
                    Preço: R$ <?= number_format($servico['MAO_DE_OBRA'], 2, ',', '.') ?>
                </div>

                <p style="text-align: center; color: #7f8c8d; margin-bottom: 20px;">
                    Esta ação não pode ser desfeita. Tem certeza que deseja continuar?
                </p>

                <form method="POST" class="form-cadastro">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-danger">Sim, Excluir Serviço</button>
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