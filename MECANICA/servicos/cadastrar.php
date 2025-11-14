<?php
require_once '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_servico = substr($_POST['nome_servico'], 0, 100);
    $mao_de_obra = str_replace(',', '.', $_POST['mao_de_obra']);
    $tempo = $_POST['tempo'];
    $descricao_servico = substr($_POST['descricao_servico'], 0, 500);
    
    try {
        $sql = "INSERT INTO SERVICO (NOME_SERVICO, MAO_DE_OBRA, TEMPO, DESCRICAO_SERVICO) 
                VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome_servico, $mao_de_obra, $tempo, $descricao_servico]);
        
        header("Location: listar.php");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao cadastrar serviço: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Serviço - Oficina</title>
    <link rel="stylesheet" href="formulario.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="header">
                <h1>🛠️ Cadastrar Serviço</h1>
                <p>Preencha os dados do novo serviço</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert error">
                    <?= $erro ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro">
                <div class="form-group">
                    <label for="nome_servico">Nome do Serviço *</label>
                    <input type="text" id="nome_servico" name="nome_servico" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label for="mao_de_obra">Preço Mão de Obra (R$) *</label>
                    <input type="text" id="mao_de_obra" name="mao_de_obra" placeholder="0.00" required>
                </div>

                <div class="form-group">
                    <label for="tempo">Tempo Estimado</label>
                    <input type="time" id="tempo" name="tempo" step="1">
                </div>

                <div class="form-group">
                    <label for="descricao_servico">Descrição</label>
                    <textarea id="descricao_servico" name="descricao_servico" rows="4" maxlength="500" placeholder="Descreva o serviço..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Cadastrar Serviço</button>
                    <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>