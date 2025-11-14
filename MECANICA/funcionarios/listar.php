<?php
require_once '../conexao.php';

// Buscar todos os funcionários
$sql = "SELECT * FROM FUNCIONARIO ORDER BY NOME_FUNCIONARIO";
$stmt = $pdo->query($sql);
$funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Funcionários - Oficina</title>
    <link rel="stylesheet" href="listas.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Lista de Funcionários</h1>
            <p>Funcionários cadastrados no sistema</p>
        </div>

        <div class="actions">
            <a href="cadastrar.php" class="btn btn-primary">+ Novo Funcionário</a>
            <a href="../index.php" class="btn btn-secondary">Voltar ao Início</a>
        </div>

        <?php if (count($funcionarios) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Telefone</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($funcionarios as $funcionario): ?>
                        <tr>
                            <td><?= $funcionario['ID_FUNCIONARIO'] ?></td>
                            <td><?= htmlspecialchars($funcionario['NOME_FUNCIONARIO']) ?></td>
                            <td><?= htmlspecialchars($funcionario['CPF_FUNCIONARIO']) ?></td>
                            <td><?= htmlspecialchars($funcionario['TELEFONE_FUNCIONARIO']) ?></td>
                            <td class="actions">
                                <a href="editar.php?id=<?= $funcionario['ID_FUNCIONARIO'] ?>" class="btn-small btn-edit">Editar</a>
                                <a href="excluir.php?id=<?= $funcionario['ID_FUNCIONARIO'] ?>" class="btn-small btn-delete" onclick="return confirm('Tem certeza que deseja excluir este funcionário?')">Excluir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="summary">
                Total de funcionários: <strong><?= count($funcionarios) ?></strong>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>Nenhum funcionário cadastrado</h3>
                <p>Comece cadastrando o primeiro funcionário no sistema.</p>
                <a href="cadastrar.php" class="btn btn-primary">Cadastrar Primeiro Funcionário</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>