<?php
require_once 'conexao.php';

try {
    // Buscar dados para as tabelas
    $clientes = $pdo->query("SELECT * FROM CLIENTE ORDER BY ID_CLIENTE DESC LIMIT 5")->fetchAll();
    $veiculos = $pdo->query("SELECT v.*, c.NOME_CLIENTE FROM VEICULO v JOIN CLIENTE c ON v.ID_CLIENTE = c.ID_CLIENTE ORDER BY v.ID_VEICULO DESC LIMIT 5")->fetchAll();
    $funcionarios = $pdo->query("SELECT * FROM FUNCIONARIO ORDER BY ID_FUNCIONARIO DESC LIMIT 5")->fetchAll();
    $servicos = $pdo->query("SELECT * FROM SERVICO ORDER BY ID_SERVICO DESC LIMIT 5")->fetchAll();
    $pecas = $pdo->query("SELECT * FROM PECAS ORDER BY ID_PECA DESC LIMIT 5")->fetchAll();
    $ordens_servico = $pdo->query("SELECT os.*, v.MARCA, v.MODELO, v.PLACA, c.NOME_CLIENTE 
                                  FROM OS os 
                                  JOIN VEICULO v ON os.ID_VEICULO = v.ID_VEICULO 
                                  JOIN CLIENTE c ON v.ID_CLIENTE = c.ID_CLIENTE 
                                  ORDER BY os.ID_OS DESC LIMIT 5")->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Oficina Mecânica</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏗️ Oficina Mecânica</h1>
            <p class="subtitle">Sistema de Gerenciamento</p>
        </header>

        <div class="dashboard">
            <!-- CARD CLIENTES -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">👥 Clientes</h2>
                    <a href="clientes/cadastrar.php" class="btn">+ Novo</a>
                </div>
                
                <?php if (count($clientes) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td title="<?= htmlspecialchars($cliente['NOME_CLIENTE']) ?>">
                                    <?= htmlspecialchars($cliente['NOME_CLIENTE']) ?>
                                </td>
                                <td><?= htmlspecialchars($cliente['TELEFONE_CLIENTE']) ?></td>
                                <td class="actions">
                                    <a href="clientes/editar.php?id=<?= $cliente['ID_CLIENTE'] ?>" class="btn-small btn-edit">Editar</a>
                                    <a href="clientes/excluir.php?id=<?= $cliente['ID_CLIENTE'] ?>" class="btn-small btn-delete" onclick="return confirm('Tem certeza?')">Excluir</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="view-all">
                        <a href="clientes/listar.php" class="btn">Ver Todos</a>
                    </div>
                <?php else: ?>
                    <div class="empty-message">Nenhum cliente</div>
                <?php endif; ?>
            </div>

            <!-- CARD VEÍCULOS -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🚗 Veículos</h2>
                    <a href="veiculos/cadastrar.php" class="btn">+ Novo</a>
                </div>
                
                <?php if (count($veiculos) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Veículo</th>
                                <th>Placa</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($veiculos as $veiculo): ?>
                            <tr>
                                <td title="<?= htmlspecialchars($veiculo['MARCA'] . ' ' . $veiculo['MODELO']) ?>">
                                    <?= htmlspecialchars($veiculo['MARCA'] . ' ' . $veiculo['MODELO']) ?>
                                </td>
                                <td><?= htmlspecialchars($veiculo['PLACA']) ?></td>
                                <td class="actions">
                                    <a href="veiculos/editar.php?id=<?= $veiculo['ID_VEICULO'] ?>" class="btn-small btn-edit">Editar</a>
                                    <a href="veiculos/excluir.php?id=<?= $veiculo['ID_VEICULO'] ?>" class="btn-small btn-delete" onclick="return confirm('Tem certeza?')">Excluir</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="view-all">
                        <a href="veiculos/listar.php" class="btn">Ver Todos</a>
                    </div>
                <?php else: ?>
                    <div class="empty-message">Nenhum veículo</div>
                <?php endif; ?>
            </div>

            <!-- CARD FUNCIONÁRIOS -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🔧 Funcionários</h2>
                    <a href="funcionarios/cadastrar.php" class="btn">+ Novo</a>
                </div>
                
                <?php if (count($funcionarios) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($funcionarios as $funcionario): ?>
                            <tr>
                                <td title="<?= htmlspecialchars($funcionario['NOME_FUNCIONARIO']) ?>">
                                    <?= htmlspecialchars($funcionario['NOME_FUNCIONARIO']) ?>
                                </td>
                                <td><?= htmlspecialchars($funcionario['TELEFONE_FUNCIONARIO']) ?></td>
                                <td class="actions">
                                    <a href="funcionarios/editar.php?id=<?= $funcionario['ID_FUNCIONARIO'] ?>" class="btn-small btn-edit">Editar</a>
                                    <a href="funcionarios/excluir.php?id=<?= $funcionario['ID_FUNCIONARIO'] ?>" class="btn-small btn-delete" onclick="return confirm('Tem certeza?')">Excluir</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="view-all">
                        <a href="funcionarios/listar.php" class="btn">Ver Todos</a>
                    </div>
                <?php else: ?>
                    <div class="empty-message">Nenhum funcionário</div>
                <?php endif; ?>
            </div>

            <!-- CARD SERVIÇOS -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🛠️ Serviços</h2>
                    <a href="servicos/cadastrar.php" class="btn">+ Novo</a>
                </div>
                
                <?php if (count($servicos) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Serviço</th>
                                <th>Preço</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servicos as $servico): ?>
                            <tr>
                                <td title="<?= htmlspecialchars($servico['NOME_SERVICO']) ?>">
                                    <?= htmlspecialchars($servico['NOME_SERVICO']) ?>
                                </td>
                                <td>R$ <?= number_format($servico['MAO_DE_OBRA'], 2, ',', '.') ?></td>
                                <td class="actions">
                                    <a href="servicos/editar.php?id=<?= $servico['ID_SERVICO'] ?>" class="btn-small btn-edit">Editar</a>
                                    <a href="servicos/excluir.php?id=<?= $servico['ID_SERVICO'] ?>" class="btn-small btn-delete" onclick="return confirm('Tem certeza?')">Excluir</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="view-all">
                        <a href="servicos/listar.php" class="btn">Ver Todos</a>
                    </div>
                <?php else: ?>
                    <div class="empty-message">Nenhum serviço</div>
                <?php endif; ?>
            </div>

            <!-- CARD PEÇAS -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">⚙️ Peças</h2>
                    <a href="pecas/cadastrar.php" class="btn">+ Novo</a>
                </div>
                
                <?php if (count($pecas) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Peça</th>
                                <th>Preço</th>
                                <th>Estoque</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pecas as $peca): ?>
                            <tr>
                                <td title="<?= htmlspecialchars($peca['NOME_PECA']) ?>">
                                    <?= htmlspecialchars($peca['NOME_PECA']) ?>
                                </td>
                                <td>R$ <?= number_format($peca['PRECO'], 2, ',', '.') ?></td>
                                <td><?= $peca['QUANTIDADE'] ?></td>
                                <td class="actions">
                                    <a href="pecas/editar.php?id=<?= $peca['ID_PECA'] ?>" class="btn-small btn-edit">Editar</a>
                                    <a href="pecas/excluir.php?id=<?= $peca['ID_PECA'] ?>" class="btn-small btn-delete" onclick="return confirm('Tem certeza?')">Excluir</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="view-all">
                        <a href="pecas/listar.php" class="btn">Ver Todos</a>
                    </div>
                <?php else: ?>
                    <div class="empty-message">Nenhuma peça</div>
                <?php endif; ?>
            </div>

            <!-- CARD ORDENS DE SERVIÇO -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📋 Ordens de Serviço</h2>
                    <a href="ordens-servico/criar.php" class="btn">+ Nova OS</a>
                </div>
                
                <?php if (count($ordens_servico) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Veículo</th>
                                <th>Cliente</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ordens_servico as $os): ?>
                            <tr>
                                <td title="<?= htmlspecialchars($os['MARCA'] . ' ' . $os['MODELO']) ?>">
                                    <?= htmlspecialchars($os['MARCA'] . ' ' . $os['MODELO']) ?>
                                </td>
                                <td><?= htmlspecialchars($os['NOME_CLIENTE']) ?></td>
                                <td>
                                    <?php 
                                    $status = $os['STATUS'] ?? 'Aberta';
                                    $status_class = '';
                                    if ($status == 'Concluída') $status_class = 'status-concluida';
                                    elseif ($status == 'Em Andamento') $status_class = 'status-andamento';
                                    else $status_class = 'status-aberta';
                                    ?>
                                    <span class="<?= $status_class ?>"><?= $status ?></span>
                                </td>
                                <td class="actions">
                                    <a href="ordens-servico/detalhes.php?id=<?= $os['ID_OS'] ?>" class="btn-small btn-edit">Ver</a>
                                    <a href="ordens-servico/editar.php?id=<?= $os['ID_OS'] ?>" class="btn-small btn-edit">Editar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="view-all">
                        <a href="ordens-servico/listar.php" class="btn">Ver Todos</a>
                    </div>
                <?php else: ?>
                    <div class="empty-message">Nenhuma OS</div>
                <?php endif; ?>
            </div>
        </div>

        <footer>
            <p>&copy; 2025 Oficina Mecânica</p>
        </footer>
    </div>
</body>
</html>