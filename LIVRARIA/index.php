<?php
// comunicação banco de dados
$mysql = mysqli_connect('localhost','root','senaisp','livraria');

// Segurança em buscar valores no banco
$columns = array('titulo','ano','preco');

$column = isset($_GET['column']) && in_array($_GET['column'],$columns) ? $_GET['column'] : $columns[0];
// Trazer dados em ordem e decrescente
$sort_order = isset($_GET['order']) && strtolower($_GET['order']) == 'desc' ? 'DESC' : 'ASC';


//verificar dados no banco
if ($result = $mysql->query('SELECT * from livros order by'. $column . ''. $sort_order)){
    // várias para a tabela
    $up_or_down = str_replace(array('ASC','DESC'), array('up','down'),$sort_order);
    $asc_or_desc = $sort_order == 'ASC' ? 'desc' : 'asc';
    $add_class = 'class="highlight"';
?>
<!DOCTYPE html>
<html>
    <head>
        <title> Banco de Dados - Códigos e Letras </title>
        <meta charset="UTF-8">
    </head>
    <body>
        <table>
            <tr>
                <th><a href="index.php?column=titulo&order=<?php echo $asc_or_desc; ?>">Titulo <?php echo $column == 'titulo' ? '-'. $up_or_down  : ''; ?> </th>
                <th><a href="index.php?column=ano&order=<?php echo $asc_or_desc; ?>">Ano <?php echo $column == 'ano' ? '-'. $up_or_down  : ''; ?> </th>
                <th><a href="index.php?column=preco&order=<?php echo $asc_or_desc; ?>">Preco <?php echo $column == 'preco' ? '-'. $up_or_down  : ''; ?> </th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td <?php echo $column == 'titulo' ? $add_class : ''; ?>> <?php echo $row ['titulo']; ?></td>
                    <td><?php echo $column == 'ano' ? $add_class : ''; ?>> <?php echo $row ['ano']; ?></td>
                    <td><?php echo $column == 'preco' ? $add_class : ''; ?>> <?php echo $row ['preco']; ?></td>
                </tr>
                <?php endwhile; ?>
        </table>
    </body>
</html>
<?php $result->free();
}
?>