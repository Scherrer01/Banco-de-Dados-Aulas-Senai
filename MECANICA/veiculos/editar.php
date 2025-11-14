<?php
require_once '../conexao.php';

// Verificar se o ID foi passado
if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$id_veiculo = $_GET['id'];

// Buscar dados do veículo
$sql = "SELECT * FROM VEICULO WHERE ID_VEICULO = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_veiculo]);
$veiculo = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se veículo existe
if (!$veiculo) {
    header("Location: listar.php");
    exit;
}

// Buscar clientes para o select
$clientes = $pdo->query("SELECT ID_CLIENTE, NOME_CLIENTE FROM CLIENTE ORDER BY NOME_CLIENTE")->fetchAll();

// Processar formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo = substr($_POST['modelo'], 0, 50);
    $marca = substr($_POST['marca'], 0, 50);
    $id_cliente = $_POST['id_cliente'];
    $ano = $_POST['ano'];
    $placa = substr($_POST['placa'], 0, 10);
    
    try {
        $sql = "UPDATE VEICULO SET 
                MODELO = ?, 
                MARCA = ?, 
                ID_CLIENTE = ?, 
                ANO = ?, 
                PLACA = ? 
                WHERE ID_VEICULO = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$modelo, $marca, $id_cliente, $ano, $placa, $id_veiculo]);
        
        header("Location: listar.php");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao atualizar veículo: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Veículo - Oficina</title>
    <link rel="stylesheet" href="editar.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="header">
                <h1>✏️ Editar Veículo</h1>
                <p>Editando dados do veículo</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert error">
                    <?= $erro ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro">
                <div class="form-group">
                    <label for="marca">Marca *</label>
                    <input type="text" id="marca" name="marca" value="<?= htmlspecialchars($veiculo['MARCA']) ?>" maxlength="50" required>
                </div>

                <div class="form-group">
                    <label for="modelo">Modelo *</label>
                    <input type="text" id="modelo" name="modelo" value="<?= htmlspecialchars($veiculo['MODELO']) ?>" maxlength="50" required>
                </div>

                <div class="form-group">
                    <label for="placa">Placa *</label>
                    <input type="text" id="placa" name="placa" value="<?= htmlspecialchars($veiculo['PLACA']) ?>" maxlength="10" required>
                </div>

                <div class="form-group">
                    <label for="ano">Ano</label>
                    <input type="number" id="ano" name="ano" value="<?= $veiculo['ANO'] ?>" min="1900" max="2030">
                </div>

                <div class="form-group">
                    <label for="id_cliente">Cliente *</label>
                    <select id="id_cliente" name="id_cliente" required>
                        <option value="">Selecione um cliente</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= $cliente['ID_CLIENTE'] ?>" 
                                <?= $cliente['ID_CLIENTE'] == $veiculo['ID_CLIENTE'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cliente['NOME_CLIENTE']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Atualizar Veículo</button>
                    <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>