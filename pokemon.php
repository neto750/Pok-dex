<?php
require_once "conexao.php";

// A página agora precisa apenas do ID. Funciona tanto com ?id=25 como ?id2=25.
$idRecebido = $_GET["id"] ?? $_GET["id2"] ?? null;
$idValidado = filter_var(
    $idRecebido,
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

if ($idValidado === false || $idValidado === null) {
    header("Location: Erro.php");
    exit;
}

$contextoApi = stream_context_create([
    "http" => ["timeout" => 8, "ignore_errors" => true]
]);
$url = "https://pokeapi.co/api/v2/pokemon/" . $idValidado;
$json = @file_get_contents($url, false, $contextoApi);

if ($json === false) {
    header("Location: Erro.php");
    exit;
}

$data = json_decode($json, true);
if (!is_array($data) || !isset($data["id"], $data["name"])) {
    header("Location: Erro.php");
    exit;
}

$id = (int) $data["id"];
$nome = (string) $data["name"];
$nomepoke = $nome;
if ($nomepoke === "toxtricity-amped") {
    $nomepoke = "toxtricity";
}
$nomepoke = ucfirst($nomepoke);
$nome = ucfirst($nome);

$sprite = $data["sprites"]["front_default"] ?? "";
$tipo = $data["types"][0]["type"]["name"] ?? "normal";
$tipo2 = $data["types"][1]["type"]["name"] ?? null;

$estatisticas = [];
foreach (($data["stats"] ?? []) as $estatistica) {
    $nomeEstatistica = $estatistica["stat"]["name"] ?? null;
    $valorEstatistica = $estatistica["base_stat"] ?? 0;
    if ($nomeEstatistica !== null) {
        $estatisticas[$nomeEstatistica] = (int) $valorEstatistica;
    }
}
$hp = $estatisticas["hp"] ?? 0;
$ata = $estatisticas["attack"] ?? 0;
$def = $estatisticas["defense"] ?? 0;

$cores = [
    "normal" => "#555555",
    "fire" => "#e25822",
    "water" => "#2878d0",
    "electric" => "#b79500",
    "grass" => "#27833b",
    "ice" => "#55bddd",
    "fighting" => "#c03028",
    "poison" => "#9341b5",
    "ground" => "#a87932",
    "flying" => "#689bd5",
    "psychic" => "#d147a3",
    "bug" => "#779c19",
    "rock" => "#8c7b3e",
    "ghost" => "#6d5a97",
    "dragon" => "#6a40bc",
    "dark" => "#5d4b43",
    "steel" => "#6f8c9e",
    "fairy" => "#c21a60"
];
$cor = $cores[strtolower($tipo)] ?? "#555555";
$cor2 = $tipo2 !== null ? ($cores[strtolower($tipo2)] ?? "#555555") : "#555555";
$tipo = ucfirst($tipo);
if ($tipo2 !== null) {
    $tipo2 = ucfirst($tipo2);
}

// Consulta preparada para saber se o Pokémon já está nos favoritos.
$stmt = $conn->prepare("SELECT pokemon_id FROM favoritos WHERE pokemon_id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();
$favoritado = $stmt->num_rows > 0;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <link rel="stylesheet" href="assets/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($nomepoke, ENT_QUOTES, "UTF-8") ?> | Pokédex</title>
</head>
<body>
    <div class="consultor1">
        <div style="display: flex; justify-content: space-around; width: 100%;">
            <p class="text">Nome: <strong><?= htmlspecialchars($nomepoke, ENT_QUOTES, "UTF-8") ?></strong> <button class="favorite" type="button" id="favorite" data-id="<?= $id ?>">☆</button></p>
            <p class="text">ID: <strong><?= $id ?></strong></p>
            <p class="text">Tipo: <strong style="color: <?= htmlspecialchars($cor, ENT_QUOTES, "UTF-8") ?>;"><?= htmlspecialchars($tipo, ENT_QUOTES, "UTF-8") ?></strong></p>
            <?php if ($tipo2 !== null): ?>
                <p class="text">Tipo: <strong style="color: <?= htmlspecialchars($cor2, ENT_QUOTES, "UTF-8") ?>;"><?= htmlspecialchars($tipo2, ENT_QUOTES, "UTF-8") ?></strong></p>
            <?php endif; ?>
            <p class="text" style="--barra: <?= min(100, ($hp / 255) * 100) ?>%;">HP: <strong><?= $hp ?></strong></p>
            <p class="text" style="--barra: <?= min(100, ($ata / 255) * 100) ?>%;">Ataque: <strong><?= $ata ?></strong></p>
            <p class="text" style="--barra: <?= min(100, ($def / 255) * 100) ?>%;">Defesa: <strong><?= $def ?></strong></p>
        </div>

        <div style="align-self: center; display: flex; flex-direction: column; align-items: center;">
            <img class="sprite" src="<?= htmlspecialchars($sprite, ENT_QUOTES, "UTF-8") ?>" alt="<?= htmlspecialchars($nomepoke, ENT_QUOTES, "UTF-8") ?>">
            <div style="display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 10px; margin-top: 190px;">
                <?php if ($id > 1): ?>
                    <a href="pokemon.php?id2=<?= $id - 1 ?>" style="padding: 10px 14px; border: 1px solid #111111; border-radius: 6px; cursor: pointer;">← Anterior</a>
                <?php endif; ?>
                <a href="pesquisa.php" style="margin-left: 0; position: static;">Pesquisar de novo</a>
                <a href="pokemon.php?id2=<?= $id + 1 ?>" style="padding: 10px 14px; border: 1px solid #111111; border-radius: 6px; cursor: pointer;">Próximo →</a>
            </div>
        </div>
    </div>

    <script>
        const botao = document.getElementById("favorite");
        let favorito = <?= $favoritado ? "true" : "false" ?>;

        function atualizarEstrela() {
            botao.textContent = favorito ? "★" : "☆";
            botao.classList.toggle("favoritado", favorito);
        }
        atualizarEstrela();

        botao.addEventListener("click", async () => {
            const dados = new URLSearchParams({
                pokemon_id: botao.dataset.id,
                acao: favorito ? "desfavoritar" : "favoritar"
            });
            botao.disabled = true;

            try {
                const resposta = await fetch("favoritos.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
                    body: dados.toString()
                });
                const texto = await resposta.text();
                if (!resposta.ok || texto.trim() !== "ok") {
                    throw new Error(texto || "Não foi possível atualizar os favoritos.");
                }
                favorito = !favorito;
                atualizarEstrela();
            } catch (erro) {
                alert(erro.message);
            } finally {
                botao.disabled = false;
            }
        });
    </script>
</body>
</html>
