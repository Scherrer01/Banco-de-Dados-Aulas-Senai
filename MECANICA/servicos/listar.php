<?php
require_once '../conexao.php';

// Buscar todos os serviços
$sql = "SELECT * FROM SERVICO ORDER BY NOME_SERVICO";
$stmt = $pdo->query($sql);
$servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Serviços - Oficina</title>
    <link rel="stylesheet" href="listas.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛠️ Lista de Serviços</h1>
            <p>Serviços cadastrados no sistema</p>
        </div>

        <div class="actions">
            <a href="cadastrar.php" class="btn btn-primary">+ Novo Serviço</a>
            <a href="../index.php" class="btn btn-secondary">Voltar ao Início</a>
        </div>

        <?php if (count($servicos) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Serviço</th>
                            <th>Preço</th>
                            <th>Tempo</th>
                            <th>Descrição</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicos as $servico): ?>
                        <tr>
                            <td><?= $servico['ID_SERVICO'] ?></td>
                            <td><?= htmlspecialchars($servico['NOME_SERVICO']) ?></td>
                            <td>R$ <?= number_format($servico['MAO_DE_OBRA'], 2, ',', '.') ?></td>
                            <td><?= $servico['TEMPO'] ?: '-' ?></td>
                            <td title="<?= htmlspecialchars($servico['DESCRICAO_SERVICO']) ?>">
                                <?= strlen($servico['DESCRICAO_SERVICO']) > 50 ? substr($servico['DESCRICAO_SERVICO'], 0, 50) . '...' : $servico['DESCRICAO_SERVICO'] ?>
                            </td>
                            <td class="actions">
                                <a href="editar.php?id=<?= $servico['ID_SERVICO'] ?>" class="btn-small btn-edit">Editar</a>
                                <a href="excluir.php?id=<?= $servico['ID_SERVICO'] ?>" class="btn-small btn-delete" onclick="return confirm('Tem certeza que deseja excluir este serviço?')">Excluir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="summary">
                Total de serviços: <strong><?= count($servicos) ?></strong>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>Nenhum serviço cadastrado</h3>
                <p>Comece cadastrando o primeiro serviço no sistema.</p>
                <a href="cadastrar.php" class="btn btn-primary">Cadastrar Primeiro Serviço</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>