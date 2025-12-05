<?php
require_once '../conexao.php';

// Verificar se o ID foi passado
if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$id_os = $_GET['id'];

// Buscar dados da OS
$sql_os = "SELECT 
                os.*,
                v.MARCA,
                v.MODELO,
                v.PLACA,
                v.ANO,
                c.NOME_CLIENTE,
                c.TELEFONE_CLIENTE,
                c.EMAIL
            FROM OS os
            JOIN VEICULO v ON os.ID_VEICULO = v.ID_VEICULO
            JOIN CLIENTE c ON v.ID_CLIENTE = c.ID_CLIENTE
            WHERE os.ID_OS = ?";
$stmt_os = $pdo->prepare($sql_os);
$stmt_os->execute([$id_os]);
$os = $stmt_os->fetch(PDO::FETCH_ASSOC);

// Verificar se OS existe
if (!$os) {
    header("Location: listar.php");
    exit;
}

// Buscar serviços da OS
$sql_servicos = "SELECT 
                    s.NOME_SERVICO,
                    s.MAO_DE_OBRA
                FROM REALIZA r
                JOIN SERVICO s ON r.ID_SERVICO = s.ID_SERVICO
                WHERE r.ID_OS = ?";
$stmt_servicos = $pdo->prepare($sql_servicos);
$stmt_servicos->execute([$id_os]);
$servicos = $stmt_servicos->fetchAll(PDO::FETCH_ASSOC);

// Buscar peças da OS
$sql_pecas = "SELECT 
                p.NOME_PECA,
                u.QUANTIDADE,
                p.PRECO,
                (u.QUANTIDADE * p.PRECO) as SUBTOTAL
            FROM UTILIZA u
            JOIN PECAS p ON u.ID_PECA = p.ID_PECA
            WHERE u.ID_OS = ?";
$stmt_pecas = $pdo->prepare($sql_pecas);
$stmt_pecas->execute([$id_os]);
$pecas = $stmt_pecas->fetchAll(PDO::FETCH_ASSOC);

// Buscar funcionários da OS - CORRIGIDO: sem ESPECIALIDADE
$sql_funcionarios = "SELECT 
                        f.NOME_FUNCIONARIO
                    FROM ATENDE a
                    JOIN FUNCIONARIO f ON a.ID_FUNCIONARIO = f.ID_FUNCIONARIO
                    WHERE a.ID_OS = ?";
$stmt_funcionarios = $pdo->prepare($sql_funcionarios);
$stmt_funcionarios->execute([$id_os]);
$funcionarios = $stmt_funcionarios->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar OS - Oficina</title>
    <link rel="stylesheet" href="visualizar.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 Ordem de Serviço #<?= $os['ID_OS'] ?></h1>
            <p>Detalhes da ordem de serviço</p>
        </div>

        <div class="actions">
            <a href="listar.php" class="btn btn-secondary">← Voltar</a>
            <a href="editar.php?id=<?= $id_os ?>" class="btn btn-edit">✏️ Editar</a>
            <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir</button>
        </div>

        <div class="os-container">
            <!-- Cabeçalho da OS -->
            <div class="os-header">
                <div class="logo">
                    <h2>Oficina Mecânica</h2>
                    <p>Rua da Oficina, 123 - Centro</p>
                    <p>Telefone: (11) 9999-9999</p>
                </div>
                <div class="os-info">
                    <h3>ORDEM DE SERVIÇO</h3>
                    <p><strong>Nº:</strong> <?= str_pad($os['ID_OS'], 6, '0', STR_PAD_LEFT) ?></p>
                    <p><strong>Data:</strong> 
                        <?php 
                        if (!empty($os['DATA_ABERTURA'])) {
                            echo date('d/m/Y', strtotime($os['DATA_ABERTURA']));
                        } else {
                            echo '--/--/----';
                        }
                        ?>
                    </p>
                    <p><strong>Status:</strong> 
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
                    </p>
                </div>
            </div>

            <!-- Informações do Cliente e Veículo -->
            <div class="section">
                <h3>👤 Cliente</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Nome:</label>
                        <span><?= htmlspecialchars($os['NOME_CLIENTE']) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Telefone:</label>
                        <span><?= htmlspecialchars($os['TELEFONE_CLIENTE']) ?></span>
                    </div>
                    <div class="info-item">
                        <label>E-mail:</label>
                        <span><?= htmlspecialchars($os['EMAIL']) ?></span>
                    </div>
                </div>
            </div>

            <div class="section">
                <h3>🚗 Veículo</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Marca/Modelo:</label>
                        <span><?= htmlspecialchars($os['MARCA']) ?> <?= htmlspecialchars($os['MODELO']) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Placa:</label>
                        <span><?= htmlspecialchars($os['PLACA']) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Ano:</label>
                        <span><?= $os['ANO'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Serviços -->
            <?php if (count($servicos) > 0): ?>
            <div class="section">
                <h3>🛠️ Serviços Realizados</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_servicos = 0;
                        foreach ($servicos as $servico): 
                            $total_servicos += $servico['MAO_DE_OBRA'];
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($servico['NOME_SERVICO']) ?></td>
                            <td>R$ <?= number_format($servico['MAO_DE_OBRA'], 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="subtotal">
                            <td><strong>Total Serviços:</strong></td>
                            <td><strong>R$ <?= number_format($total_servicos, 2, ',', '.') ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Peças -->
            <?php if (count($pecas) > 0): ?>
            <div class="section">
                <h3>⚙️ Peças Utilizadas</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Peça</th>
                            <th>Quantidade</th>
                            <th>Valor Unitário</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_pecas = 0;
                        foreach ($pecas as $peca): 
                            $subtotal = $peca['QUANTIDADE'] * $peca['PRECO'];
                            $total_pecas += $subtotal;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($peca['NOME_PECA']) ?></td>
                            <td><?= $peca['QUANTIDADE'] ?></td>
                            <td>R$ <?= number_format($peca['PRECO'], 2, ',', '.') ?></td>
                            <td>R$ <?= number_format($subtotal, 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="subtotal">
                            <td colspan="3"><strong>Total Peças:</strong></td>
                            <td><strong>R$ <?= number_format($total_pecas, 2, ',', '.') ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Funcionários -->
            <?php if (count($funcionarios) > 0): ?>
            <div class="section">
                <h3>🔧 Funcionários Responsáveis</h3>
                <div class="funcionarios-grid">
                    <?php foreach ($funcionarios as $funcionario): ?>
                    <div class="funcionario-card">
                        <h4><?= htmlspecialchars($funcionario['NOME_FUNCIONARIO']) ?></h4>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Total e Observações -->
            <div class="section total-section">
                <div class="total-info">
                    <div class="total-label">TOTAL DA OS:</div>
                    <div class="total-value">R$ <?= number_format($os['TOTAL'], 2, ',', '.') ?></div>
                </div>
            </div>

            <?php if (!empty($os['OBS'])): ?>
            <div class="section">
                <h3>📝 Observações</h3>
                <div class="observacoes">
                    <?= nl2br(htmlspecialchars($os['OBS'])) ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Rodapé -->
            <div class="footer">
                <div class="assinatura">
                    <p>_________________________________</p>
                    <p>Responsável Técnico</p>
                </div>
                <div class="data-conclusao">
                    <?php if (!empty($os['DATA_CONCLUSAO'])): ?>
                    <p><strong>Concluído em:</strong> <?= date('d/m/Y', strtotime($os['DATA_CONCLUSAO'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Estilos gerais */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 2.5rem;
        }

        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .actions {
            padding: 20px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            color: white;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #7f8c8d 0%, #95a5a6 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .btn-edit {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
        }

        .btn-edit:hover {
            background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.2);
        }

        /* Container da OS */
        .os-container {
            padding: 30px;
        }

        .os-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .logo h2 {
            margin: 0;
            color: #2c3e50;
        }

        .logo p {
            margin: 5px 0;
            color: #7f8c8d;
        }

        .os-info {
            text-align: right;
        }

        .os-info h3 {
            margin: 0;
            color: #3498db;
            font-size: 1.5rem;
        }

        .os-info p {
            margin: 8px 0;
            color: #2c3e50;
        }

        /* Seções */
        .section {
            margin: 25px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .section h3 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-item label {
            font-weight: bold;
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .info-item span {
            color: #2c3e50;
            font-size: 16px;
        }

        /* Tabelas */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .items-table th {
            background: #3498db;
            color: white;
            padding: 12px;
            text-align: left;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .items-table tr.subtotal {
            background: #f1f2f6;
            font-weight: bold;
        }

        /* Funcionários */
        .funcionarios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .funcionario-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }

        .funcionario-card h4 {
            margin: 0;
            color: #2c3e50;
        }

        /* Total */
        .total-section {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;
            color: white;
            margin-top: 30px;
        }

        .total-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 24px;
        }

        .total-label {
            font-weight: bold;
        }

        .total-value {
            font-weight: bold;
            color: #2ecc71;
        }

        /* Observações */
        .observacoes {
            background: white;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
            line-height: 1.6;
        }

        /* Rodapé */
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }

        .assinatura {
            text-align: center;
        }

        .assinatura p {
            margin: 5px 0;
            color: #7f8c8d;
        }

        /* Status */
        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
        }

        .status-aberta {
            background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
            color: #d35400;
        }

        .status-concluida {
            background: linear-gradient(135deg, #55efc4 0%, #00b894 100%);
            color: white;
        }

        .status-andamento {
            background: linear-gradient(135deg, #81ecec 0%, #00cec9 100%);
            color: white;
        }

        .status-cancelada {
            background: linear-gradient(135deg, #fab1a0 0%, #e17055 100%);
            color: white;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .os-header {
                flex-direction: column;
                text-align: center;
            }
            
            .os-info {
                text-align: center;
                margin-top: 20px;
            }
            
            .footer {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .total-info {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }

        @media print {
            .actions, .btn {
                display: none;
            }
            
            .os-container {
                box-shadow: none;
                padding: 0;
            }
            
            .section {
                page-break-inside: avoid;
            }
        }
    </style>
</body>
</html>