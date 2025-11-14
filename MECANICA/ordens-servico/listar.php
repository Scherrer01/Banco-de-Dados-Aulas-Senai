<?php
require_once '../conexao.php';

// Buscar dados para os selects
$veiculos = $pdo->query("SELECT v.*, c.NOME_CLIENTE FROM VEICULO v JOIN CLIENTE c ON v.ID_CLIENTE = c.ID_CLIENTE ORDER BY c.NOME_CLIENTE")->fetchAll();
$servicos = $pdo->query("SELECT * FROM SERVICO ORDER BY NOME_SERVICO")->fetchAll();
$pecas = $pdo->query("SELECT * FROM PECAS ORDER BY NOME_PECA")->fetchAll();
$funcionarios = $pdo->query("SELECT * FROM FUNCIONARIO ORDER BY NOME_FUNCIONARIO")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Dados básicos da OS
        $id_veiculo = $_POST['id_veiculo'];
        $observacoes = substr($_POST['observacoes'], 0, 500);
        
        // Inserir OS
        $sql_os = "INSERT INTO OS (ID_VEICULO, OBS, TOTAL, STATUS) VALUES (?, ?, 0, 'Aberta')";
        $stmt_os = $pdo->prepare($sql_os);
        $stmt_os->execute([$id_veiculo, $observacoes]);
        $id_os = $pdo->lastInsertId();
        
        $total_os = 0;
        
        // Processar serviços selecionados
        if (isset($_POST['servicos']) && is_array($_POST['servicos'])) {
            foreach ($_POST['servicos'] as $id_servico) {
                $servico = $pdo->query("SELECT MAO_DE_OBRA FROM SERVICO WHERE ID_SERVICO = $id_servico")->fetch();
                $total_os += $servico['MAO_DE_OBRA'];
                
                $sql_servico = "INSERT INTO REALIZA (ID_OS, ID_SERVICO) VALUES (?, ?)";
                $stmt_servico = $pdo->prepare($sql_servico);
                $stmt_servico->execute([$id_os, $id_servico]);
            }
        }
        
        // Processar peças selecionadas
        if (isset($_POST['pecas']) && is_array($_POST['pecas'])) {
            foreach ($_POST['pecas'] as $id_peca) {
                $peca = $pdo->query("SELECT PRECO FROM PECAS WHERE ID_PECA = $id_peca")->fetch();
                $quantidade = $_POST['quantidade_peca'][$id_peca] ?? 1;
                $total_os += $peca['PRECO'] * $quantidade;
                
                $sql_peca = "INSERT INTO UTILIZA (ID_OS, ID_PECA, QUANTIDADE) VALUES (?, ?, ?)";
                $stmt_peca = $pdo->prepare($sql_peca);
                $stmt_peca->execute([$id_os, $id_peca, $quantidade]);
                
                // Atualizar estoque
                $sql_estoque = "UPDATE PECAS SET QUANTIDADE = QUANTIDADE - ? WHERE ID_PECA = ?";
                $stmt_estoque = $pdo->prepare($sql_estoque);
                $stmt_estoque->execute([$quantidade, $id_peca]);
            }
        }
        
        // Processar funcionários selecionados
        if (isset($_POST['funcionarios']) && is_array($_POST['funcionarios'])) {
            foreach ($_POST['funcionarios'] as $id_funcionario) {
                $sql_funcionario = "INSERT INTO ATENDE (ID_OS, ID_FUNCIONARIO) VALUES (?, ?)";
                $stmt_funcionario = $pdo->prepare($sql_funcionario);
                $stmt_funcionario->execute([$id_os, $id_funcionario]);
            }
        }
        
        // Atualizar total da OS
        $sql_update_total = "UPDATE OS SET TOTAL = ? WHERE ID_OS = ?";
        $stmt_update = $pdo->prepare($sql_update_total);
        $stmt_update->execute([$total_os, $id_os]);
        
        $pdo->commit();
        header("Location: listar.php?sucesso=1");
        exit;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $erro = "Erro ao criar ordem de serviço: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Ordem de Serviço - Oficina</title>
    <link rel="stylesheet" href="listar.css">
    
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="header">
                <h1>📋 Nova Ordem de Serviço</h1>
                <p>Crie uma nova ordem de serviço</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert error">
                    <?= $erro ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro">
                <!-- Seção Veículo -->
                <div class="secao">
                    <h3>🚗 Veículo</h3>
                    <div class="form-group">
                        <label for="id_veiculo">Selecione o Veículo *</label>
                        <select id="id_veiculo" name="id_veiculo" required>
                            <option value="">Selecione um veículo</option>
                            <?php foreach ($veiculos as $veiculo): ?>
                                <option value="<?= $veiculo['ID_VEICULO'] ?>">
                                    <?= htmlspecialchars($veiculo['NOME_CLIENTE']) ?> - 
                                    <?= htmlspecialchars($veiculo['MARCA'] . ' ' . $veiculo['MODELO']) ?> - 
                                    <?= htmlspecialchars($veiculo['PLACA']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Seção Serviços -->
                <div class="secao">
                    <h3>🛠️ Serviços</h3>
                    <div class="checkbox-group">
                        <?php foreach ($servicos as $servico): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" id="servico_<?= $servico['ID_SERVICO'] ?>" 
                                       name="servicos[]" value="<?= $servico['ID_SERVICO'] ?>">
                                <label for="servico_<?= $servico['ID_SERVICO'] ?>">
                                    <?= htmlspecialchars($servico['NOME_SERVICO']) ?>
                                </label>
                                <span class="preco">R$ <?= number_format($servico['MAO_DE_OBRA'], 2, ',', '.') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Seção Peças -->
                <div class="secao">
                    <h3>⚙️ Peças</h3>
                    <div class="checkbox-group">
                        <?php foreach ($pecas as $peca): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" id="peca_<?= $peca['ID_PECA'] ?>" 
                                       name="pecas[]" value="<?= $peca['ID_PECA'] ?>"
                                       onchange="toggleQuantidade(this, <?= $peca['ID_PECA'] ?>)">
                                <label for="peca_<?= $peca['ID_PECA'] ?>">
                                    <?= htmlspecialchars($peca['NOME_PECA']) ?>
                                </label>
                                <span class="preco">R$ <?= number_format($peca['PRECO'], 2, ',', '.') ?></span>
                                <input type="number" name="quantidade_peca[<?= $peca['ID_PECA'] ?>]" 
                                       class="quantidade-input" value="1" min="1" 
                                       max="<?= $peca['QUANTIDADE'] ?>" 
                                       id="quantidade_<?= $peca['ID_PECA'] ?>" 
                                       style="display: none;">
                                <small style="color: #7f8c8d; margin-left: 5px;">
                                    (<?= $peca['QUANTIDADE'] ?> em estoque)
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Seção Funcionários -->
                <div class="secao">
                    <h3>🔧 Funcionários Responsáveis</h3>
                    <div class="checkbox-group">
                        <?php foreach ($funcionarios as $funcionario): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" id="funcionario_<?= $funcionario['ID_FUNCIONARIO'] ?>" 
                                       name="funcionarios[]" value="<?= $funcionario['ID_FUNCIONARIO'] ?>">
                                <label for="funcionario_<?= $funcionario['ID_FUNCIONARIO'] ?>">
                                    <?= htmlspecialchars($funcionario['NOME_FUNCIONARIO']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Observações -->
                <div class="form-group">
                    <label for="observacoes">Observações</label>
                    <textarea id="observacoes" name="observacoes" rows="3" maxlength="500" placeholder="Observações sobre o serviço..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Criar Ordem de Serviço</button>
                    <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleQuantidade(checkbox, idPeca) {
            const quantidadeInput = document.getElementById('quantidade_' + idPeca);
            quantidadeInput.style.display = checkbox.checked ? 'block' : 'none';
        }
    </script>
</body>
</html>