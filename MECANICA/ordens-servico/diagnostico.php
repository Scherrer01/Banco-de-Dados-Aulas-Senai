<?php
require_once '../conexao.php';

echo "<h2>Diagnóstico da Tabela OS</h2>";

try {
    // Verificar se tabela existe
    $tabela = $pdo->query("SHOW TABLES LIKE 'OS'")->fetch();
    if (!$tabela) {
        echo "<p style='color: red'>Tabela OS não existe!</p>";
        exit;
    }
    
    echo "<p>Tabela OS existe ✓</p>";
    
    // Verificar colunas
    $colunas = $pdo->query("DESCRIBE OS");
    echo "<h3>Colunas da tabela OS:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . $coluna['Field'] . "</td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . $coluna['Default'] . "</td>";
        echo "<td>" . $coluna['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar se STATUS existe
    $status_col = $pdo->query("SHOW COLUMNS FROM OS LIKE 'STATUS'")->fetch();
    if (!$status_col) {
        echo "<p style='color: red'>Coluna STATUS não existe na tabela OS!</p>";
        
        // Tentar adicionar
        echo "<p>Tentando adicionar coluna STATUS...</p>";
        try {
            $pdo->exec("ALTER TABLE OS ADD COLUMN STATUS VARCHAR(50) DEFAULT 'Aberta'");
            echo "<p style='color: green'>Coluna STATUS adicionada com sucesso!</p>";
        } catch (Exception $e) {
            echo "<p style='color: red'>Erro ao adicionar coluna: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: green'>Coluna STATUS existe ✓</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red'>Erro: " . $e->getMessage() . "</p>";
}
?>