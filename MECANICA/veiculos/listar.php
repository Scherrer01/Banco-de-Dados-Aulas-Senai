<?php
require_once '../conexao.php';

// Buscar todos os veículos com nome do cliente
$sql = "SELECT v.*, c.NOME_CLIENTE 
        FROM VEICULO v 
        JOIN CLIENTE c ON v.ID_CLIENTE = c.ID_CLIENTE 
        ORDER BY v.MARCA, v.MODELO";
$stmt = $pdo->query($sql);
$veiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Veículos - Oficina</title>
    <link rel="stylesheet" href="listas.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚗 Lista de Veículos</h1>
            <p>Veículos cadastrados no sistema</p>
        </div>

        <div class="actions">
            <a href="cadastrar.php" class="btn btn-primary">+ Novo Veículo</a>
            <a href="../index.php" class="btn btn-secondary">Voltar ao Início</a>
        </div>

        <?php if (count($veiculos) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Marca/Modelo</th>
                            <th>Placa</th>
                            <th>Ano</th>
                            <th>Cliente</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($veiculos as $veiculo): ?>
                        <tr>
                            <td><?= $veiculo['ID_VEICULO'] ?></td>
                            <td><?= htmlspecialchars($veiculo['MARCA'] . ' ' . $veiculo['MODELO']) ?></td>
                            <td><?= htmlspecialchars($veiculo['PLACA']) ?></td>
                            <td><?= $veiculo['ANO'] ?: '-' ?></td>
                            <td><?= htmlspecialchars($veiculo['NOME_CLIENTE']) ?></td>
                            <td class="actions">
                                <a href="editar.php?id=<?= $veiculo['ID_VEICULO'] ?>" class="btn-small btn-edit">Editar</a>
                                <a href="excluir.php?id=<?= $veiculo['ID_VEICULO'] ?>" class="btn-small btn-delete" onclick="return confirm('Tem certeza que deseja excluir este veículo?')">Excluir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="summary">
                Total de veículos: <strong><?= count($veiculos) ?></strong>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>Nenhum veículo cadastrado</h3>
                <p>Comece cadastrando o primeiro veículo no sistema.</p>
                <a href="cadastrar.php" class="btn btn-primary">Cadastrar Primeiro Veículo</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>