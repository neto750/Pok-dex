<?php
require_once "conexao.php";
$acao = $_POST["acao"];
$pokemon_id = $_POST["pokemon_id"];
if ($acao == "favoritar") {
    $sql = "INSERT INTO favoritos (pokemon_id) VALUES ($pokemon_id)";
    $resultado = $conn->query($sql);
} elseif ($acao == "desfavoritar") {
    $sql = "DELETE FROM favoritos WHERE pokemon_id = $pokemon_id";
    $resultado = $conn->query($sql);
    if (!$resultado) {
        echo $conn->error;
    } else {
        echo "deu certo";
    }
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="assets/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokemon</title>
</head>

<body>
    <div class="consultor1">
        <div style="display: flex; justify-content: space-around; width: 100%;">

        </div>

    </div>
</body>

</html>