<?php
require_once '../conexao.php';

// Verificar se o ID foi passado
if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$id_os = $_GET['id'];

// Buscar dados da OS
$sql_os = "SELECT os.*, v.MARCA, v.MODELO, v.PLACA, c.NOME_CLIENTE 
           FROM OS os 
           JOIN VEICULO v ON os.ID_VEICULO = v.ID_VEICULO 
           JOIN CLIENTE c ON v.ID_CLIENTE = c.ID_CLIENTE 
           WHERE os.ID_OS = ?";
$stmt_os = $pdo->prepare($sql_os);
$stmt_os->execute([$id_os]);
$os = $stmt_os->fetch(PDO::FETCH_ASSOC);

if (!$os) {
    header("Location: listar.php");
    exit;
}

// Determinar o status (com fallback para 'Aberta' se não existir)
$status = isset($os['STATUS']) ? $os['STATUS'] : 'Aberta';

// Verificar se já está concluída
if ($status == 'Concluída') {
    header("Location: detalhes.php?id=" . $id_os);
    exit;
}

// Processar conclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sql = "UPDATE OS SET STATUS = 'Concluída', DATA_CONCLUSAO = CURDATE() WHERE ID_OS = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_os]);
        
        header("Location: detalhes.php?id=" . $id_os);
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao concluir ordem de serviço: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concluir OS - Oficina</title>
    <link rel="stylesheet" href="concluir.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="header">
                <h1>✅ Concluir Ordem de Serviço</h1>
                <p>Confirmação de conclusão</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert error">
                    <?= $erro ?>
                </div>
            <?php endif; ?>

            <div class="alert info">
                <strong>Confirma conclusão da OS #<?= $os['ID_OS'] ?>?</strong><br><br>
                <strong>Veículo:</strong> <?= htmlspecialchars($os['MARCA'] . ' ' . $os['MODELO']) ?> - <?= htmlspecialchars($os['PLACA']) ?><br>
                <strong>Cliente:</strong> <?= htmlspecialchars($os['NOME_CLIENTE']) ?><br>
                <strong>Total:</strong> R$ <?= number_format($os['TOTAL'], 2, ',', '.') ?><br>
                <strong>Status Atual:</strong> <?= $status ?>
            </div>

            <p class="confirmation-text">
                Ao concluir, a ordem de serviço será marcada como finalizada e não poderá mais ser editada.
            </p>

            <form method="POST" class="form-cadastro">
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Sim, Concluir OS</button>
                    <a href="detalhes.php?id=<?= $id_os ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>