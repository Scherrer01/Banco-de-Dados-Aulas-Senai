<?php
$conn = new mysqli(
         "localhost",
         "root",
         "senaisp",
         "livraria"
);

$id = $_POST['id_usuario'];
$nome = $_POST['nome'];
$row = $_POST['email'];

$sql = "UPDATE usuarios SET nome = '$nome', email = '$email' WHERE id='$id' ";

if ($conn->query($sql) === TRUE) {
    echo "Dados atualizados com sucesso!";
    echo "<br><a href='index.html'>Voltar</a>";
} else {
    echo "Erro: " . $conn->error;
}

$conn->close();
?>