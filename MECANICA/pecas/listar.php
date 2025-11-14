<?php
require_once '../conexao.php';

// Buscar todas as peças
$sql = "SELECT * FROM PECAS ORDER BY NOME_PECA";
$stmt = $pdo->query($sql);
$pecas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Peças - Oficina</title>
    <link rel="stylesheet" href="listas.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ Lista de Peças</h1>
            <p>Peças cadastradas no estoque</p>
        </div>

        <div class="actions">
            <a href="cadastrar.php" class="btn btn-primary">+ Nova Peça</a>
            <a href="../index.php" class="btn btn-secondary">Voltar ao Início</a>
        </div>

        <?php if (count($pecas) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Peça</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                            <th>Descrição</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pecas as $peca): ?>
                        <tr>
                            <td><?= $peca['ID_PECA'] ?></td>
                            <td><?= htmlspecialchars($peca['NOME_PECA']) ?></td>
                            <td>R$ <?= number_format($peca['PRECO'], 2, ',', '.') ?></td>
                            <td>
                                <span class="<?= $peca['QUANTIDADE'] == 0 ? 'estoque-zero' : ($peca['QUANTIDADE'] < 10 ? 'estoque-baixo' : 'estoque-normal') ?>">
                                    <?= $peca['QUANTIDADE'] ?>
                                </span>
                            </td>
                            <td title="<?= htmlspecialchars($peca['DESCRICAO_PECA']) ?>">
                                <?= strlen($peca['DESCRICAO_PECA']) > 50 ? substr($peca['DESCRICAO_PECA'], 0, 50) . '...' : $peca['DESCRICAO_PECA'] ?>
                            </td>
                            <td class="actions">
                                <a href="editar.php?id=<?= $peca['ID_PECA'] ?>" class="btn-small btn-edit">Editar</a>
                                <a href="excluir.php?id=<?= $peca['ID_PECA'] ?>" class="btn-small btn-delete" onclick="return confirm('Tem certeza que deseja excluir esta peça?')">Excluir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="summary">
                Total de peças: <strong><?= count($pecas) ?></strong> | 
                Valor total em estoque: <strong>R$ <?= number_format(array_sum(array_column($pecas, 'PRECO')), 2, ',', '.') ?></strong>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>Nenhuma peça cadastrada</h3>
                <p>Comece cadastrando a primeira peça no sistema.</p>
                <a href="cadastrar.php" class="btn btn-primary">Cadastrar Primeira Peça</a>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .estoque-zero { color: #e74c3c; font-weight: bold; }
        .estoque-baixo { color: #f39c12; font-weight: bold; }
        .estoque-normal { color: #27ae60; }
    </style>
</body>
</html>