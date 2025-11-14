<?php
require_once '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = substr($_POST['nome'], 0, 100);
    $cpf = substr($_POST['cpf'], 0, 14);
    $telefone = substr($_POST['telefone'], 0, 15);
    
    try {
        $sql = "INSERT INTO FUNCIONARIO (NOME_FUNCIONARIO, CPF_FUNCIONARIO, TELEFONE_FUNCIONARIO) 
                VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $cpf, $telefone]);
        
        header("Location: listar.php");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao cadastrar funcionário: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Funcionário - Oficina</title>
    <link rel="stylesheet" href="formulario.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="header">
                <h1>🔧 Cadastrar Funcionário</h1>
                <p>Preencha os dados do novo funcionário</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert error">
                    <?= $erro ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-cadastro">
                <div class="form-group">
                    <label for="nome">Nome Completo *</label>
                    <input type="text" id="nome" name="nome" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label for="cpf">CPF *</label>
                    <input type="text" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00" required>
                </div>

                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone" maxlength="15" placeholder="(00) 00000-0000">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Cadastrar Funcionário</button>
                    <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>