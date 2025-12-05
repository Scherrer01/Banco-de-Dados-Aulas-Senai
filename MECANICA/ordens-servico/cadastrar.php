<?php
require_once '../conexao.php';

// Buscar dados para os selects
$veiculos = $pdo->query("SELECT v.*, c.NOME_CLIENTE FROM VEICULO v JOIN CLIENTE c ON v.ID_CLIENTE = c.ID_CLIENTE ORDER BY c.NOME_CLIENTE")->fetchAll();
$servicos = $pdo->query("SELECT * FROM SERVICO ORDER BY NOME_SERVICO")->fetchAll();
$pecas = $pdo->query("SELECT * FROM PECAS ORDER BY NOME_PECA")->fetchAll();
$funcionarios = $pdo->query("SELECT * FROM FUNCIONARIO ORDER BY NOME_FUNCIONARIO")->fetchAll();

// TESTE: Verificar estrutura da tabela OS
try {
    $teste = $pdo->query("SHOW COLUMNS FROM OS LIKE 'STATUS'");
    $coluna_existe = $teste->fetch();
    
    if (!$coluna_existe) {
        // Coluna não existe - vamos tentar criar
        $pdo->exec("ALTER TABLE OS ADD COLUMN STATUS VARCHAR(50) DEFAULT 'Aberta'");
        error_log("Coluna STATUS adicionada à tabela OS");
    }
} catch (Exception $e) {
    error_log("Erro ao verificar coluna STATUS: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Dados básicos da OS
        $id_veiculo = $_POST['id_veiculo'];
        $observacoes = substr($_POST['observacoes'], 0, 500);
        
        // OPÇÃO 1: Inserir sem STATUS (se a coluna não existir)
        try {
            // Tenta inserir com STATUS
            $sql_os = "INSERT INTO OS (ID_VEICULO, OBS, TOTAL, STATUS, DATA_ABERTURA) VALUES (?, ?, 0, 'Aberta', CURDATE())";
            $stmt_os = $pdo->prepare($sql_os);
            $stmt_os->execute([$id_veiculo, $observacoes]);
        } catch (PDOException $e) {
            // Se falhar, tenta sem STATUS
            $sql_os = "INSERT INTO OS (ID_VEICULO, OBS, TOTAL, DATA_ABERTURA) VALUES (?, ?, 0, CURDATE())";
            $stmt_os = $pdo->prepare($sql_os);
            $stmt_os->execute([$id_veiculo, $observacoes]);
        }
        
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
                $peca = $pdo->query("SELECT PRECO, QUANTIDADE FROM PECAS WHERE ID_PECA = $id_peca")->fetch();
                $quantidade = $_POST['quantidade_peca'][$id_peca] ?? 1;
                
                // Verificar estoque
                if ($quantidade > $peca['QUANTIDADE']) {
                    throw new Exception("Estoque insuficiente. Disponível: {$peca['QUANTIDADE']}");
                }
                
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
        
        // Verificar se pelo menos um serviço ou peça foi selecionado
        if (!isset($_POST['servicos']) && !isset($_POST['pecas'])) {
            throw new Exception("Selecione pelo menos um serviço ou uma peça.");
        }
        
        // Verificar se pelo menos um funcionário foi selecionado
        if (!isset($_POST['funcionarios'])) {
            throw new Exception("Selecione pelo menos um funcionário.");
        }
        
        // Atualizar total da OS
        $sql_update_total = "UPDATE OS SET TOTAL = ? WHERE ID_OS = ?";
        $stmt_update = $pdo->prepare($sql_update_total);
        $stmt_update->execute([$total_os, $id_os]);
        
        $pdo->commit();
        header("Location: listar.php?sucesso=1");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $erro = "Erro ao criar ordem de serviço: " . $e->getMessage();
        error_log("ERRO OS: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Ordem de Serviço - Oficina</title>
    <link rel="stylesheet" href="cadastrar.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="header">
                <h1>📋 Nova Ordem de Serviço</h1>
                <p>Preencha os dados da nova ordem de serviço</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert error">
                    <?= $erro ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['sucesso'])): ?>
                <div class="alert success">
                    Ordem de serviço criada com sucesso!
                </div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro" id="formOS">
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
                    <p class="section-desc">Selecione os serviços a serem realizados</p>
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
                    <p class="section-desc">Selecione as peças a serem utilizadas</p>
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
                                <small class="estoque">(<?= $peca['QUANTIDADE'] ?> em estoque)</small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Seção Funcionários -->
                <div class="secao">
                    <h3>🔧 Funcionários Responsáveis</h3>
                    <p class="section-desc">Selecione os funcionários que atenderão esta OS</p>
                    <div class="checkbox-group">
                        <?php foreach ($funcionarios as $funcionario): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" id="funcionario_<?= $funcionario['ID_FUNCIONARIO'] ?>" 
                                       name="funcionarios[]" value="<?= $funcionario['ID_FUNCIONARIO'] ?>">
                                <label for="funcionario_<?= $funcionario['ID_FUNCIONARIO'] ?>">
                                    <?= htmlspecialchars($funcionario['NOME_FUNCIONARIO']) ?>
                                </label>
                                <?php if (!empty($funcionario['ESPECIALIDADE'])): ?>
                                    <small class="especialidade">(<?= htmlspecialchars($funcionario['ESPECIALIDADE']) ?>)</small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Observações -->
                <div class="form-group">
                    <label for="observacoes">Observações</label>
                    <textarea id="observacoes" name="observacoes" rows="3" maxlength="500" 
                              placeholder="Observações sobre o serviço, problemas identificados, etc..."></textarea>
                    <small class="char-count">Máximo 500 caracteres</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" onclick="return validarForm()">Criar Ordem de Serviço</button>
                    <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleQuantidade(checkbox, idPeca) {
            const quantidadeInput = document.getElementById('quantidade_' + idPeca);
            quantidadeInput.style.display = checkbox.checked ? 'inline-block' : 'none';
        }
        
        function validarForm() {
            const veiculo = document.getElementById('id_veiculo').value;
            if (!veiculo) {
                alert('Por favor, selecione um veículo!');
                return false;
            }
            
            const servicos = document.querySelectorAll('input[name="servicos[]"]:checked');
            const pecas = document.querySelectorAll('input[name="pecas[]"]:checked');
            const funcionarios = document.querySelectorAll('input[name="funcionarios[]"]:checked');
            
            if (servicos.length === 0 && pecas.length === 0) {
                alert('Selecione pelo menos um serviço ou uma peça!');
                return false;
            }
            
            if (funcionarios.length === 0) {
                alert('Selecione pelo menos um funcionário!');
                return false;
            }
            
            return true;
        }
    </script>

    <style>
        .secao {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
        .secao h3 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .section-desc {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }
        .checkbox-item {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        .checkbox-item:hover {
            border-color: #3498db;
            box-shadow: 0 2px 5px rgba(52, 152, 219, 0.2);
        }
        .checkbox-item input[type="checkbox"] {
            margin-right: 10px;
        }
        .checkbox-item label {
            flex-grow: 1;
            cursor: pointer;
            font-weight: 500;
        }
        .preco {
            background: #2ecc71;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
            margin-left: 10px;
        }
        .estoque {
            color: #7f8c8d;
            margin-left: 10px;
            font-size: 12px;
        }
        .especialidade {
            color: #3498db;
            margin-left: 10px;
            font-size: 12px;
            font-style: italic;
        }
        .quantidade-input {
            width: 60px;
            margin-left: 10px;
            padding: 4px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .char-count {
            display: block;
            text-align: right;
            color: #7f8c8d;
            font-size: 12px;
            margin-top: 5px;
        }
        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</body>
</html>