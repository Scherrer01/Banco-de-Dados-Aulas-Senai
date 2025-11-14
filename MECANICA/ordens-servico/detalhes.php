<?php
require_once '../conexao.php';

// Verificar se o ID foi passado
if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$id_os = $_GET['id'];

// Buscar dados da OS
$sql_os = "SELECT os.*, v.MARCA, v.MODELO, v.PLACA, c.NOME_CLIENTE, c.TELEFONE_CLIENTE 
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

// Buscar serviços da OS
$sql_servicos = "SELECT s.* FROM SERVICO s 
                 JOIN REALIZA r ON s.ID_SERVICO = r.ID_SERVICO 
                 WHERE r.ID_OS = ?";
$stmt_servicos = $pdo->prepare($sql_servicos);
$stmt_servicos->execute([$id_os]);
$servicos = $stmt_servicos->fetchAll();

// Buscar peças da OS
$sql_pecas = "SELECT p.*, u.QUANTIDADE FROM PECAS p 
              JOIN UTILIZA u ON p.ID_PECA = u.ID_PECA 
              WHERE u.ID_OS = ?";
$stmt_pecas = $pdo->prepare($sql_pecas);
$stmt_pecas->execute([$id_os]);
$pecas = $stmt_pecas->fetchAll();

// Buscar funcionários da OS
$sql_funcionarios = "SELECT f.* FROM FUNCIONARIO f 
                     JOIN ATENDE a ON f.ID_FUNCIONARIO = a.ID_FUNCIONARIO 
                     WHERE a.ID_OS = ?";
$stmt_funcionarios = $pdo->prepare($sql_funcionarios);
$stmt_funcionarios->execute([$id_os]);
$funcionarios = $stmt_funcionarios->fetchAll();

// Determinar o status (com fallback para 'Aberta' se não existir)
$status = isset($os['STATUS']) ? $os['STATUS'] : 'Aberta';
$status_class = 'status-' . strtolower(str_replace(' ', '-', $status));
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da OS - Oficina</title>
    <link rel="stylesheet" href="detalhes.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Detalhes da Ordem de Serviço #<?= $os['ID_OS'] ?></h1>
            <p>Informações completas da ordem de serviço</p>
        </div>

        <div class="actions">
            <a href="listar.php" class="btn btn-secondary">← Voltar para Lista</a>
            <a href="editar.php?id=<?= $os['ID_OS'] ?>" class="btn btn-primary">Editar OS</a>
            <?php if ($status != 'Concluída'): ?>
                <a href="concluir.php?id=<?= $os['ID_OS'] ?>" class="btn btn-success">Concluir OS</a>
            <?php endif; ?>
        </div>

        <div class="detalhes-container">
            <!-- Informações Básicas -->
            <div class="info-grid">
                <div class="info-card">
                    <h4>📅 Informações da OS</h4>
                    <p><strong>Número:</strong> #<?= $os['ID_OS'] ?></p>
                    <p><strong>Data Abertura:</strong> <?= date('d/m/Y', strtotime($os['DATA_ABERTURA'])) ?></p>
                    <p><strong>Data Conclusão:</strong> <?= $os['DATA_CONCLUSAO'] ? date('d/m/Y', strtotime($os['DATA_CONCLUSAO'])) : 'Em andamento' ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge <?= $status_class ?>">
                            <?= $status ?>
                        </span>
                    </p>
                </div>

                <div class="info-card">
                    <h4>🚗 Veículo</h4>
                    <p><strong>Cliente:</strong> <?= htmlspecialchars($os['NOME_CLIENTE']) ?></p>
                    <p><strong>Telefone:</strong> <?= htmlspecialchars($os['TELEFONE_CLIENTE']) ?></p>
                    <p><strong>Veículo:</strong> <?= htmlspecialchars($os['MARCA'] . ' ' . $os['MODELO']) ?></p>
                    <p><strong>Placa:</strong> <?= htmlspecialchars($os['PLACA']) ?></p>
                </div>
            </div>

            <!-- Serviços -->
            <div class="info-card">
                <h4>🛠️ Serviços Realizados</h4>
                <div class="lista-itens">
                    <?php if (count($servicos) > 0): ?>
                        <?php foreach ($servicos as $servico): ?>
                            <div class="item">
                                <span><?= htmlspecialchars($servico['NOME_SERVICO']) ?></span>
                                <span>R$ <?= number_format($servico['MAO_DE_OBRA'], 2, ',', '.') ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-state">Nenhum serviço cadastrado</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Peças -->
            <div class="info-card">
                <h4>⚙️ Peças Utilizadas</h4>
                <div class="lista-itens">
                    <?php if (count($pecas) > 0): ?>
                        <?php foreach ($pecas as $peca): ?>
                            <div class="item">
                                <span><?= htmlspecialchars($peca['NOME_PECA']) ?> (x<?= $peca['QUANTIDADE'] ?>)</span>
                                <span>R$ <?= number_format($peca['PRECO'] * $peca['QUANTIDADE'], 2, ',', '.') ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-state">Nenhuma peça utilizada</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Funcionários -->
            <div class="info-card">
                <h4>🔧 Funcionários Responsáveis</h4>
                <div class="lista-itens">
                    <?php if (count($funcionarios) > 0): ?>
                        <?php foreach ($funcionarios as $funcionario): ?>
                            <div class="item">
                                <span><?= htmlspecialchars($funcionario['NOME_FUNCIONARIO']) ?></span>
                                <span><?= htmlspecialchars($funcionario['TELEFONE_FUNCIONARIO']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-state">Nenhum funcionário atribuído</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Observações -->
            <?php if (!empty($os['OBS'])): ?>
                <div class="info-card">
                    <h4>📝 Observações</h4>
                    <p><?= nl2br(htmlspecialchars($os['OBS'])) ?></p>
                </div>
            <?php endif; ?>

            <!-- Total -->
            <div class="total">
                TOTAL: R$ <?= number_format($os['TOTAL'], 2, ',', '.') ?>
            </div>
        </div>
    </div>
</body>
</html>