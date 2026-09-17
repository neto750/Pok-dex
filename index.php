<?php
//IF para pegar os dados e pesquisar na api o pokemon, depois passar pra outra tela
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //aqui eu cato o id que o usuario colocou no input
    $id1 = $_POST["id1"];

    //aqui eu cato o nome que o usuario colocou no input

    $nome = $_POST["nome"];
    if ($nome == "toxtricity" || $nome == "Toxtricity") {
        $nome = "Toxtricity-amped";
    }

    //se o id1 nao tiver vazio, ai cria uma variavel com o nome de url na api + id1 (id q o usuario escolheu)
    if (!empty($id1)) {
        $url = "https://pokeapi.co/api/v2/pokemon/" . $id1;
        //botar o conteudo encontrado dentro de uma variavel e transformar em manipulavel pelo php
        $json = @file_get_contents($url);
        //Se o conteudo nao existir/for falso vai pra tela de erro (ou seja se tiver vazio)
        if ($json === false) {
            header("location: Erro.php");
            exit;
        }

        //mesmo processo aqui, só que com o nome
    } elseif (!empty($nome)) {
        $url = "https://pokeapi.co/api/v2/pokemon/" . $nome;
        $json = @file_get_contents($url);
        if ($json === false) {
            header("location: Erro.php");
            exit;
        }
    }
    //Aqui é mt importante pra caso ambos os campos estiverem vazios (oque antes nao acionava nada)
    //agora aciona este if, que verifica se os 2 tao vazios e joga pra pagina de erro.
    if (empty($id1) && empty($nome)) {
        header("location: Erro.php");
        exit;
    }

    //aqui eu transformo o data em array pra poder consultar a api.
    $data = json_decode($json, true);

    //pego o nome do pokemon (caso o usuario use o id, essa parte é essencial)
    $nomepoke = $data["name"];

    //aqui pego o sprite
    $sprite = $data["sprites"]["front_default"];

    //aqui o id
    $id = $data["id"];

    //aqui hp, ataque e defesa
    $hp = $data["stats"]["0"]["base_stat"];
    $ata = $data["stats"]["1"]["base_stat"];
    $def = $data["stats"]["2"]["base_stat"];

    //tipo (tipo1)
    $tipo = $data["types"][0]["type"]["name"];

    //aqui eu verifico se existe um segundo tipo no pokemon, ou seja se ele tem uma array 0 e uma 1
    //se tiver um segundo tipo, o $tipo2 equivale a este tipo e ele joga todo mundo pro pokemon.php com os 2 tipos
    if (isset($data["types"][1])) {
        $tipo2 = $data["types"][1]["type"]["name"];
        header("location: pokemon.php?nome=$nome&id=$id&sprite=$sprite&tipo=$tipo&tipo2=$tipo2&nomepoke=$nomepoke&def=$def&ata=$ata&hp=$hp");
        //caso nao tenha 2 tipos, só jogar todo mundo pro pokemon.php sem o segundo tipo
    } else {
        //ai depois precisa do header normal ne, caso ele tenha so 1 tipagem
        header("location: pokemon.php?nome=$nome&id=$id&sprite=$sprite&tipo=$tipo&nomepoke=$nomepoke&def=$def&ata=$ata&hp=$hp");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="assets/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokedex</title>
</head>

<body>
    <div class="consultor">
        <h1>Insira o nome ou ID do seu pokemon!</h1>
        <form method="post">
            <input class="nome" type="number" name="id1" placeholder="Insira o ID">
            <input class="nome" name="nome" placeholder="Insira o nome">
            <button type="submit" class="nome">Pesquisar</button>
        </form>
    </div>
</body>

</html>