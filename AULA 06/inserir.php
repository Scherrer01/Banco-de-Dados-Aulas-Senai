<?php
$nome = $_POST['nome'];
$email = $_POST['email'];

$conn = new mysqli("localhost", "root", "senaisp", "livraria");

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

$stmt = $conn->prepare("INSERT INTO usuarios (nome, email) VALUES (?, ?)");
$stmt->bind_param("ss", $nome, $email);

if ($stmt->execute()) {
    // Redireciona antes de qualquer saída
    header("Location: index.html");
    exit;
} else {
    echo "Erro ao salvar os dados: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

<Style>

</Style>

