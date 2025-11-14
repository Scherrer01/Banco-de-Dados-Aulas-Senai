<?php
require_once '../conexao.php';

// Buscar todos os clientes
$sql = "SELECT * FROM CLIENTE ORDER BY NOME_CLIENTE";
$stmt = $pdo->query($sql);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Clientes - Oficina</title>
    <link rel="stylesheet" href="listar.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👥 Lista de Clientes</h1>
            <p>Clientes cadastrados no sistema</p>
        </div>

        <div class="actions">
            <a href="cadastrar.php" class="btn btn-primary">+ Novo Cliente</a>
            <a href="../index.php" class="btn btn-secondary">Voltar ao Início</a>
        </div>

        <?php if (count($clientes) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Telefone</th>
                            <th>E-mail</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?= $cliente['ID_CLIENTE'] ?></td>
                            <td><?= htmlspecialchars($cliente['NOME_CLIENTE']) ?></td>
                            <td><?= htmlspecialchars($cliente['CPF_CLIENTE']) ?></td>
                            <td><?= htmlspecialchars($cliente['TELEFONE_CLIENTE']) ?></td>
                            <td><?= htmlspecialchars($cliente['EMAIL']) ?></td>
                            <td class="actions">
                                <a href="editar.php?id=<?= $cliente['ID_CLIENTE'] ?>" class="btn-small btn-edit">Editar</a>
                                <a href="excluir.php?id=<?= $cliente['ID_CLIENTE'] ?>" class="btn-small btn-delete" onclick="return confirm('Tem certeza que deseja excluir este cliente?')">Excluir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="summary">
                Total de clientes: <strong><?= count($clientes) ?></strong>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>Nenhum cliente cadastrado</h3>
                <p>Comece cadastrando o primeiro cliente no sistema.</p>
                <a href="cadastrar.php" class="btn btn-primary">Cadastrar Primeiro Cliente</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>