<?php
require_once '../conexao.php';

// Verificar se o ID foi passado
if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$id_os = $_GET['id'];

// Buscar dados da OS para confirmar
$sql = "SELECT 
            os.*,
            v.MARCA,
            v.MODELO,
            v.PLACA,
            c.NOME_CLIENTE
        FROM OS os
        JOIN VEICULO v ON os.ID_VEICULO = v.ID_VEICULO
        JOIN CLIENTE c ON v.ID_CLIENTE = c.ID_CLIENTE
        WHERE os.ID_OS = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_os]);
$os = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se OS existe
if (!$os) {
    header("Location: listar.php");
    exit;
}

// Processar exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Remover relações
        $pdo->exec("DELETE FROM REALIZA WHERE ID_OS = $id_os");
        $pdo->exec("DELETE FROM UTILIZA WHERE ID_OS = $id_os");
        $pdo->exec("DELETE FROM ATENDE WHERE ID_OS = $id_os");
        
        // Remover OS
        $sql = "DELETE FROM OS WHERE ID_OS = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_os]);
        
        $pdo->commit();
        header("Location: listar.php");
        exit;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $erro = "Erro ao excluir ordem de serviço: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Ordem de Serviço - Oficina</title>
    <link rel="stylesheet" href="cadastrar.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="header">
                <h1>🗑️ Excluir Ordem de Serviço</h1>
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
                    <strong>Atenção!</strong> Você está prestes a excluir a ordem de serviço:<br><br>
                    <div class="os-info">
                        <p><strong>OS #<?= $os['ID_OS'] ?></strong></p>
                        <p><strong>Cliente:</strong> <?= htmlspecialchars($os['NOME_CLIENTE']) ?></p>
                        <p><strong>Veículo:</strong> <?= htmlspecialchars($os['MARCA']) ?> <?= htmlspecialchars($os['MODELO']) ?> - <?= htmlspecialchars($os['PLACA']) ?></p>
                        <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($os['DATA_ABERTURA'])) ?></p>
                        <p><strong>Status:</strong> <?= $os['STATUS'] ?></p>
                        <p><strong>Total:</strong> R$ <?= number_format($os['TOTAL'], 2, ',', '.') ?></p>
                    </div>
                </div>

                <p style="text-align: center; color: #7f8c8d; margin-bottom: 20px;">
                    Esta ação não pode ser desfeita. Todos os serviços, peças e funcionários vinculados serão removidos.<br>
                    Tem certeza que deseja continuar?
                </p>

                <form method="POST" class="form-cadastro">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-danger">Sim, Excluir OS</button>
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
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #c0392b, #e74c3c);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }
        
        .os-info {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
            border: 1px solid #ddd;
        }
        
        .os-info p {
            margin: 5px 0;
        }
    </style>
</body>
</html>