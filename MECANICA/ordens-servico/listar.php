<?php
require_once '../conexao.php';

// Buscar todas as OS com informações relacionadas
$sql = "SELECT 
            os.*,
            v.MARCA,
            v.MODELO,
            v.PLACA,
            c.NOME_CLIENTE
        FROM OS os
        JOIN VEICULO v ON os.ID_VEICULO = v.ID_VEICULO
        JOIN CLIENTE c ON v.ID_CLIENTE = c.ID_CLIENTE
        ORDER BY os.ID_OS DESC";
$stmt = $pdo->query($sql);
$ordens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Ordens de Serviço - Oficina</title>
    <link rel="stylesheet" href="listar.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Lista de Ordens de Serviço</h1>
            <p>Ordens de serviço cadastradas no sistema</p>
        </div>

        <div class="actions">
            <a href="cadastrar.php" class="btn btn-primary">+ Nova OS</a>
            <a href="../index.php" class="btn btn-secondary">Voltar ao Início</a>
        </div>

        <?php if (count($ordens) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Veículo</th>
                            <th>Data Abertura</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordens as $os): ?>
                        <tr>
                            <td><?= $os['ID_OS'] ?></td>
                            <td><?= htmlspecialchars($os['NOME_CLIENTE']) ?></td>
                            <td>
                                <?= htmlspecialchars($os['MARCA']) ?> 
                                <?= htmlspecialchars($os['MODELO']) ?> - 
                                <?= htmlspecialchars($os['PLACA']) ?>
                            </td>
                            <td>
                                <?php 
                                // Corrigir o erro de data NULL
                                if (!empty($os['DATA_ABERTURA'])) {
                                    echo date('d/m/Y', strtotime($os['DATA_ABERTURA']));
                                } else {
                                    echo '--/--/----';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                $status = $os['STATUS'] ?? 'Aberta';
                                $status_class = '';
                                
                                if (strtolower($status) == 'concluída' || strtolower($status) == 'concluida') {
                                    $status_class = 'status-concluida';
                                    $status = 'Concluída';
                                } elseif (strtolower($status) == 'em andamento' || strtolower($status) == 'andamento') {
                                    $status_class = 'status-andamento';
                                    $status = 'Em andamento';
                                } elseif (strtolower($status) == 'cancelada') {
                                    $status_class = 'status-cancelada';
                                } else {
                                    $status_class = 'status-aberta';
                                    $status = 'Aberta';
                                }
                                ?>
                                <span class="status <?= $status_class ?>">
                                    <?= $status ?>
                                </span>
                            </td>
                            <td>R$ <?= number_format($os['TOTAL'] ?? 0, 2, ',', '.') ?></td>
                            <td class="actions">
                                <a href="visualizar.php?id=<?= $os['ID_OS'] ?>" class="btn-small btn-view">Visualizar</a>
                                <a href="editar.php?id=<?= $os['ID_OS'] ?>" class="btn-small btn-edit">Editar</a>
                                <a href="excluir.php?id=<?= $os['ID_OS'] ?>" class="btn-small btn-delete" onclick="return confirm('Tem certeza que deseja excluir esta OS?')">Excluir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="summary">
                <?php 
                $total_os = count($ordens);
                $abertas = array_filter($ordens, function($os) {
                    $status = $os['STATUS'] ?? 'Aberta';
                    return strtolower($status) == 'aberta';
                });
                $concluidas = array_filter($ordens, function($os) {
                    $status = $os['STATUS'] ?? 'Aberta';
                    return strtolower($status) == 'concluída' || strtolower($status) == 'concluida';
                });
                ?>
                Total de OS: <strong><?= $total_os ?></strong>
                <?php if (count($abertas) > 0): ?>
                    • Em aberto: <strong><?= count($abertas) ?></strong>
                <?php endif; ?>
                <?php if (count($concluidas) > 0): ?>
                    • Concluídas: <strong><?= count($concluidas) ?></strong>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>Nenhuma ordem de serviço cadastrada</h3>
                <p>Comece criando a primeira ordem de serviço no sistema.</p>
                <a href="cadastrar.php" class="btn btn-primary">Criar Primeira OS</a>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }
        .status-aberta {
            background: #ffeaa7;
            color: #d35400;
        }
        .status-concluida {
            background: #55efc4;
            color: #00b894;
        }
        .status-andamento {
            background: #81ecec;
            color: #0984e3;
        }
        .status-cancelada {
            background: #fab1a0;
            color: #d63031;
        }
        .btn-view {
            background: #3498db;
            color: white;
        }
        .btn-view:hover {
            background: #2980b9;
        }
        .btn-edit {
            background: #f39c12;
            color: white;
        }
        .btn-edit:hover {
            background: #e67e22;
        }
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        .btn-delete:hover {
            background: #c0392b;
        }
        .actions {
            white-space: nowrap;
        }
        .actions a {
            margin: 0 2px;
        }
    </style>
</body>
</html>