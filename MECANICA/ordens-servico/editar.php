<?php
require_once '../conexao.php';

// Verificar se o ID foi passado
if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$id_os = $_GET['id'];

// Buscar dados da OS
$sql_os = "SELECT * FROM OS WHERE ID_OS = ?";
$stmt_os = $pdo->prepare($sql_os);
$stmt_os->execute([$id_os]);
$os = $stmt_os->fetch(PDO::FETCH_ASSOC);

// Verificar se OS existe
if (!$os) {
    header("Location: listar.php");
    exit;
}

// Buscar dados para os selects
$veiculos = $pdo->query("SELECT v.*, c.NOME_CLIENTE FROM VEICULO v JOIN CLIENTE c ON v.ID_CLIENTE = c.ID_CLIENTE ORDER BY c.NOME_CLIENTE")->fetchAll();
$servicos = $pdo->query("SELECT * FROM SERVICO ORDER BY NOME_SERVICO")->fetchAll();
$pecas = $pdo->query("SELECT * FROM PECAS ORDER BY NOME_PECA")->fetchAll();
$funcionarios = $pdo->query("SELECT * FROM FUNCIONARIO ORDER BY NOME_FUNCIONARIO")->fetchAll();

// Buscar serviços selecionados
$sql_servicos_selecionados = "SELECT ID_SERVICO FROM REALIZA WHERE ID_OS = ?";
$stmt_servicos = $pdo->prepare($sql_servicos_selecionados);
$stmt_servicos->execute([$id_os]);
$servicos_selecionados = $stmt_servicos->fetchAll(PDO::FETCH_COLUMN);

// Buscar peças selecionadas
$sql_pecas_selecionadas = "SELECT ID_PECA, QUANTIDADE FROM UTILIZA WHERE ID_OS = ?";
$stmt_pecas = $pdo->prepare($sql_pecas_selecionadas);
$stmt_pecas->execute([$id_os]);
$pecas_selecionadas = $stmt_pecas->fetchAll(PDO::FETCH_ASSOC);

// Buscar funcionários selecionados
$sql_funcionarios_selecionados = "SELECT ID_FUNCIONARIO FROM ATENDE WHERE ID_OS = ?";
$stmt_funcionarios = $pdo->prepare($sql_funcionarios_selecionados);
$stmt_funcionarios->execute([$id_os]);
$funcionarios_selecionados = $stmt_funcionarios->fetchAll(PDO::FETCH_COLUMN);

// Processar formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Dados básicos da OS
        $id_veiculo = $_POST['id_veiculo'];
        $observacoes = substr($_POST['observacoes'], 0, 500);
        $status = $_POST['status'];
        
        // Atualizar dados básicos da OS
        $sql_update = "UPDATE OS SET ID_VEICULO = ?, OBS = ?, STATUS = ? WHERE ID_OS = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$id_veiculo, $observacoes, $status, $id_os]);
        
        // Se for concluída, adicionar data de conclusão
        if ($status == 'Concluída' && empty($os['DATA_CONCLUSAO'])) {
            $sql_conclusao = "UPDATE OS SET DATA_CONCLUSAO = CURDATE() WHERE ID_OS = ?";
            $stmt_conclusao = $pdo->prepare($sql_conclusao);
            $stmt_conclusao->execute([$id_os]);
        }
        
        // Remover relações antigas
        $pdo->exec("DELETE FROM REALIZA WHERE ID_OS = $id_os");
        $pdo->exec("DELETE FROM UTILIZA WHERE ID_OS = $id_os");
        $pdo->exec("DELETE FROM ATENDE WHERE ID_OS = $id_os");
        
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
                $total_os += $peca['PRECO'] * $quantidade;
                
                $sql_peca = "INSERT INTO UTILIZA (ID_OS, ID_PECA, QUANTIDADE) VALUES (?, ?, ?)";
                $stmt_peca = $pdo->prepare($sql_peca);
                $stmt_peca->execute([$id_os, $id_peca, $quantidade]);
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
        $stmt_update_total = $pdo->prepare($sql_update_total);
        $stmt_update_total->execute([$total_os, $id_os]);
        
        $pdo->commit();
        header("Location: listar.php?sucesso=1");
        exit;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $erro = "Erro ao atualizar ordem de serviço: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ordem de Serviço - Oficina</title>
    <link rel="stylesheet" href="editar.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="header">
                <h1>✏️ Editar Ordem de Serviço #<?= $os['ID_OS'] ?></h1>
                <p>Editando dados da ordem de serviço</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert error">
                    <?= $erro ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro" id="formOS">
                <!-- Seção Status -->
                <div class="form-group">
                    <label for="status">Status da OS *</label>
                    <select id="status" name="status" required>
                        <option value="Aberta" <?= ($os['STATUS'] ?? 'Aberta') == 'Aberta' ? 'selected' : '' ?>>Aberta</option>
                        <option value="Em andamento" <?= ($os['STATUS'] ?? 'Aberta') == 'Em andamento' ? 'selected' : '' ?>>Em andamento</option>
                        <option value="Concluída" <?= ($os['STATUS'] ?? 'Aberta') == 'Concluída' ? 'selected' : '' ?>>Concluída</option>
                        <option value="Cancelada" <?= ($os['STATUS'] ?? 'Aberta') == 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
                    </select>
                </div>

                <!-- Seção Veículo -->
                <div class="secao">
                    <h3>🚗 Veículo</h3>
                    <div class="form-group">
                        <label for="id_veiculo">Selecione o Veículo *</label>
                        <select id="id_veiculo" name="id_veiculo" required>
                            <option value="">Selecione um veículo</option>
                            <?php foreach ($veiculos as $veiculo): ?>
                                <option value="<?= $veiculo['ID_VEICULO'] ?>" <?= $os['ID_VEICULO'] == $veiculo['ID_VEICULO'] ? 'selected' : '' ?>>
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
                                       name="servicos[]" value="<?= $servico['ID_SERVICO'] ?>"
                                       <?= in_array($servico['ID_SERVICO'], $servicos_selecionados) ? 'checked' : '' ?>>
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
                        <?php foreach ($pecas as $peca): 
                            $quantidade_selecionada = 1;
                            $selecionado = false;
                            
                            foreach ($pecas_selecionadas as $peca_sel) {
                                if ($peca_sel['ID_PECA'] == $peca['ID_PECA']) {
                                    $quantidade_selecionada = $peca_sel['QUANTIDADE'];
                                    $selecionado = true;
                                    break;
                                }
                            }
                        ?>
                            <div class="checkbox-item">
                                <input type="checkbox" id="peca_<?= $peca['ID_PECA'] ?>" 
                                       name="pecas[]" value="<?= $peca['ID_PECA'] ?>"
                                       onchange="toggleQuantidade(this, <?= $peca['ID_PECA'] ?>)"
                                       <?= $selecionado ? 'checked' : '' ?>>
                                <label for="peca_<?= $peca['ID_PECA'] ?>">
                                    <?= htmlspecialchars($peca['NOME_PECA']) ?>
                                </label>
                                <span class="preco">R$ <?= number_format($peca['PRECO'], 2, ',', '.') ?></span>
                                <input type="number" name="quantidade_peca[<?= $peca['ID_PECA'] ?>]" 
                                       class="quantidade-input" value="<?= $quantidade_selecionada ?>" min="1" 
                                       max="<?= $peca['QUANTIDADE'] ?>" 
                                       id="quantidade_<?= $peca['ID_PECA'] ?>" 
                                       style="display: <?= $selecionado ? 'inline-block' : 'none' ?>;">
                                <small class="estoque">(<?= $peca['QUANTIDADE'] ?> em estoque)</small>
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
                                       name="funcionarios[]" value="<?= $funcionario['ID_FUNCIONARIO'] ?>"
                                       <?= in_array($funcionario['ID_FUNCIONARIO'], $funcionarios_selecionados) ? 'checked' : '' ?>>
                                <label for="funcionario_<?= $funcionario['ID_FUNCIONARIO'] ?>">
                                    <?= htmlspecialchars($funcionario['NOME_FUNCIONARIO']) ?>
                                </label>
                                <!-- Removida a exibição da ESPECIALIDADE -->
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Observações -->
                <div class="form-group">
                    <label for="observacoes">Observações</label>
                    <textarea id="observacoes" name="observacoes" rows="3" maxlength="500"><?= htmlspecialchars($os['OBS'] ?? '') ?></textarea>
                    <small class="char-count">Máximo 500 caracteres</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Atualizar OS</button>
                    <a href="visualizar.php?id=<?= $id_os ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleQuantidade(checkbox, idPeca) {
            const quantidadeInput = document.getElementById('quantidade_' + idPeca);
            quantidadeInput.style.display = checkbox.checked ? 'inline-block' : 'none';
        }
        
        // Exibir campos de quantidade para peças já selecionadas
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[name="pecas[]"]');
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    const idPeca = checkbox.value;
                    const quantidadeInput = document.getElementById('quantidade_' + idPeca);
                    if (quantidadeInput) {
                        quantidadeInput.style.display = 'inline-block';
                    }
                }
            });
        });
    </script>

    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .form-wrapper {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        .alert.error {
            background: #ffeaa7;
            color: #d35400;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #fdcb6e;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .secao {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
        }
        
        .secao h3 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }
        
        .checkbox-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .checkbox-item:hover {
            border-color: #3498db;
            box-shadow: 0 2px 10px rgba(52, 152, 219, 0.1);
        }
        
        .checkbox-item input[type="checkbox"] {
            margin-right: 12px;
            width: 18px;
            height: 18px;
        }
        
        .checkbox-item label {
            flex-grow: 1;
            cursor: pointer;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .preco {
            background: #2ecc71;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            margin-left: 10px;
        }
        
        .estoque {
            color: #7f8c8d;
            margin-left: 10px;
            font-size: 12px;
        }
        
        .quantidade-input {
            width: 70px;
            margin-left: 10px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            text-align: center;
        }
        
        .char-count {
            display: block;
            text-align: right;
            color: #7f8c8d;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .form-actions {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .btn {
            padding: 14px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 150px;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            color: white;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #7f8c8d 0%, #95a5a6 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(149, 165, 166, 0.3);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .form-wrapper {
                padding: 20px;
            }
            
            .checkbox-group {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</body>
</html>