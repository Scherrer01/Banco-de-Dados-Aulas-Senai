<?php
require_once '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_peca = substr($_POST['nome_peca'], 0, 100);
    $preco = str_replace(',', '.', $_POST['preco']);
    $quantidade = intval($_POST['quantidade']);
    $descricao_peca = substr($_POST['descricao_peca'], 0, 500);
    
    try {
        $sql = "INSERT INTO PECAS (NOME_PECA, PRECO, QUANTIDADE, DESCRICAO_PECA) 
                VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome_peca, $preco, $quantidade, $descricao_peca]);
        
        header("Location: listar.php");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao cadastrar peça: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Peça - Oficina</title>
    <link rel="stylesheet" href="formulario.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="header">
                <h1>⚙️ Cadastrar Peça</h1>
                <p>Preencha os dados da nova peça</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert error">
                    <?= $erro ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro">
                <div class="form-group">
                    <label for="nome_peca">Nome da Peça *</label>
                    <input type="text" id="nome_peca" name="nome_peca" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label for="preco">Preço (R$) *</label>
                    <input type="text" id="preco" name="preco" placeholder="0.00" required>
                </div>

                <div class="form-group">
                    <label for="quantidade">Quantidade em Estoque *</label>
                    <input type="number" id="quantidade" name="quantidade" min="0" value="0" required>
                </div>

                <div class="form-group">
                    <label for="descricao_peca">Descrição</label>
                    <textarea id="descricao_peca" name="descricao_peca" rows="4" maxlength="500" placeholder="Descreva a peça..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Cadastrar Peça</button>
                    <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>