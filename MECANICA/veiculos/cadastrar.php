<?php
require_once '../conexao.php';

// Buscar clientes para o select
$clientes = $pdo->query("SELECT ID_CLIENTE, NOME_CLIENTE FROM CLIENTE ORDER BY NOME_CLIENTE")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo = substr($_POST['modelo'], 0, 50);
    $marca = substr($_POST['marca'], 0, 50);
    $id_cliente = $_POST['id_cliente'];
    $ano = $_POST['ano'];
    $placa = substr($_POST['placa'], 0, 10);
    
    try {
        $sql = "INSERT INTO VEICULO (MODELO, MARCA, ID_CLIENTE, ANO, PLACA) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$modelo, $marca, $id_cliente, $ano, $placa]);
        
        header("Location: listar.php");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao cadastrar veículo: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Veículo - Oficina</title>
    <link rel="stylesheet" href="formulario.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="header">
                <h1>🚗 Cadastrar Veículo</h1>
                <p>Preencha os dados do novo veículo</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert error">
                    <?= $erro ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro">
                <div class="form-group">
                    <label for="marca">Marca *</label>
                    <input type="text" id="marca" name="marca" maxlength="50" required>
                </div>

                <div class="form-group">
                    <label for="modelo">Modelo *</label>
                    <input type="text" id="modelo" name="modelo" maxlength="50" required>
                </div>

                <div class="form-group">
                    <label for="placa">Placa *</label>
                    <input type="text" id="placa" name="placa" maxlength="10" placeholder="ABC1D23" required>
                </div>

                <div class="form-group">
                    <label for="ano">Ano</label>
                    <input type="number" id="ano" name="ano" min="1900" max="2030" placeholder="2024">
                </div>

                <div class="form-group">
                    <label for="id_cliente">Cliente *</label>
                    <select id="id_cliente" name="id_cliente" required>
                        <option value="">Selecione um cliente</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= $cliente['ID_CLIENTE'] ?>">
                                <?= htmlspecialchars($cliente['NOME_CLIENTE']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Cadastrar Veículo</button>
                    <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>