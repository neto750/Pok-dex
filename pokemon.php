<?php
//Criaçao das variaveis usando $_GET pra pegar do header.

$id = $_GET["id"];


if (isset($_GET["id"])) {
    $nome = $_GET["nome"];
    $nomepoke = $_GET["nomepoke"];
    $sprite = $_GET["sprite"];
    $tipo = $_GET["tipo"];
    $ata = $_GET["ata"];
    $def = $_GET["def"];
    $hp = $_GET["hp"];
    $cores = [
        "fire" => "red",
        "water" => "blue",
        "rock" => "gray",
        "electric" => "#fbea2b",
        "grass" => "green",
        "flying" => "white",
        "poison" => "#cb62ff",
        "dark" => "#5d018b",
        "ground" => "brown",
        "bug" => "#88ffff",
        "fairy" => "#c21a60",
        "ghost" => "#e1e1e1",
        "steel" => "#5ca0ff",
        "psychic" => "#ff5fe7",
        "dragon" => "#fd6c1e",
        "normal" => "black",
    ];
    $cor = $cores[$tipo];
    //Se o tipo2 nao for uma variavel nula, entao pegar ele e deixar a primeira letra dele maiuscula
    if (isset($_GET["tipo2"])) {
        $tipo2 = $_GET["tipo2"];
        $cor2 = $cores[$tipo2];
        $tipo2 = ucfirst($tipo2);
    }


    //Aqui só deixei as informaçoes das variaveis em maiusculo.
    $id = ucfirst($id);
    $nome = ucfirst($nome);
    $nomepoke = ucfirst($nomepoke);
    $tipo = ucfirst($tipo);



    //Detalhe importante pois o toxtricity tinha um nome diferente, entao simplifiquei ele
    //Se o nome do pokemon encontrado for esse entao simplifico ele
    if ($nomepoke == "Toxtricity-amped" || $nomepoke == "toxtricity-amped") {
        $nomepoke = "Toxtricity";
    }
    
}elseif (!isset($_GET["id"])){
    $id2 = $_GET["id2"];
    if($id2 > $id ) {
    $url = "https://pokeapi.co/api/v2/pokemon/" . $id2;
    //botar o conteudo encontrado dentro de uma variavel e transformar em manipulavel pelo php
    $json = @file_get_contents($url);
    //Se o conteudo nao existir/for falso vai pra tela de erro (ou seja se tiver vazio)
    if ($json === false) {
        header("location: Erro.php");
        exit;
    }
    $data = json_decode($json, true);
    $nome = $data["name"];
    $nomepoke = $data["name"];

    //aqui pego o sprite
    $sprite = $data["sprites"]["front_default"];

    //aqui o id
    $id = $data["id"];

    //aqui hp, ataque e defesa
    $hp = $data["stats"]["0"]["base_stat"];
    $ata = $data["stats"]["1"]["base_stat"];
    $def = $data["stats"]["2"]["base_stat"];

    //tipo, mesmo esquema da outra page
    $tipo = $data["types"][0]["type"]["name"];
        if (isset($data["types"][1])) {
        $tipo2 = $data["types"][1]["type"]["name"];
        header("location: pokemon.php?nome=$nome&id=$id&sprite=$sprite&tipo=$tipo&tipo2=$tipo2&nomepoke=$nomepoke&def=$def&ata=$ata&hp=$hp");
        //caso nao tenha 2 tipos, só jogar todo mundo pro pokemon.php sem o segundo tipo
    } else {
        //ai depois precisa do header normal ne, caso ele tenha so 1 tipagem
        header("location: pokemon.php?nome=$nome&id=$id&sprite=$sprite&tipo=$tipo&nomepoke=$nomepoke&def=$def&ata=$ata&hp=$hp");
    }
}elseif ($id2 < $id ) {
    $url = "https://pokeapi.co/api/v2/pokemon/" . $id2;
    //botar o conteudo encontrado dentro de uma variavel e transformar em manipulavel pelo php
    $json = @file_get_contents($url);
    //Se o conteudo nao existir/for falso vai pra tela de erro (ou seja se tiver vazio)
    if ($json === false) {
        header("location: Erro.php");
        exit;
    }
    $data = json_decode($json, true);
    $nome = $data["name"];
    $nomepoke = $data["name"];

    //aqui pego o sprite
    $sprite = $data["sprites"]["front_default"];

    //aqui o id
    $id = $data["id"];

    //aqui hp, ataque e defesa
    $hp = $data["stats"]["0"]["base_stat"];
    $ata = $data["stats"]["1"]["base_stat"];
    $def = $data["stats"]["2"]["base_stat"];

    //tipo, mesmo esquema da outra page
    $tipo = $data["types"][0]["type"]["name"];
        if (isset($data["types"][1])) {
        $tipo2 = $data["types"][1]["type"]["name"];
        header("location: pokemon.php?nome=$nome&id=$id&sprite=$sprite&tipo=$tipo&tipo2=$tipo2&nomepoke=$nomepoke&def=$def&ata=$ata&hp=$hp");
        //caso nao tenha 2 tipos, só jogar todo mundo pro pokemon.php sem o segundo tipo
    } else {
        //ai depois precisa do header normal ne, caso ele tenha so 1 tipagem
        header("location: pokemon.php?nome=$nome&id=$id&sprite=$sprite&tipo=$tipo&nomepoke=$nomepoke&def=$def&ata=$ata&hp=$hp");
    }
}}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokemon</title>
</head>

<body>
    <div class="consultor1">
        <div style="display: flex; justify-content: space-around; width: 100%;">
            <p class="text">Nome: <strong><?php echo $nomepoke ?></strong></p>
            <p class="text">ID: <strong><?php echo $id ?></strong></p>
            <p class="text">Tipo: <strong style="color: <?= $cor ?>;"><?php echo $tipo ?></strong></p>
            <!-- Aqui pra baixo tem uma checagem pra ver se existe um segundo tipo,
            se existir ele mostra o segundo tipo, de resto to só mostrando as variaveis-->
            <?php if (isset($tipo2)) { ?>
                <p class="text">Tipo: <strong style="color: <?= $cor2 ?>;"><?php echo $tipo2 ?></strong></p>
            <?php } ?>
            <p class="text" style="--barra: <?= min(100, ($hp / 255) * 100) ?>%;">HP: <strong><?php echo $hp ?></strong></p>
            <p class="text" style="--barra: <?= min(100, ($ata / 255) * 100) ?>%;">Ataque: <strong><?php echo $ata ?></strong></p>
            <p class="text" style="--barra: <?= min(100, ($def / 255) * 100) ?>%;">Defesa: <strong><?php echo $def ?></strong></p>
        </div>
        <!-- Criei um botao pra pesquisar novamente bem simples com uma div pra ficar legal -->
        <div style="align-self: center; display: flex; flex-direction: column; align-items: center;">
            <img class="sprite" src="<?= $sprite ?>" alt="sprite">
            <div style="display: flex; align-items: center; gap: 10px; margin-top: 190px;">
                <a href="pokemon.php?id2=<?=$id - 1 ?>" style="padding: 10px 14px; border: 1px solid #111111; border-radius: 6px; cursor: pointer;">← Anterior</a>
                <a href="index.php" style="margin-left: 0; position: static;">Pesquisar Denovo</a>
                <a href="pokemon.php?id2=<?=$id + 1 ?>" style="padding: 10px 14px; border: 1px solid #111111; border-radius: 6px; cursor: pointer;">Próximo →</a>
            </div>
        </div>
    </div>
</body>

</html>