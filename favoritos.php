<?php
require_once "conexao.php";

// Endpoint utilizado pela estrela da página pokemon.php e pelos botões desta página.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: text/plain; charset=UTF-8");

    if (!isset($_POST["acao"], $_POST["pokemon_id"])) {
        http_response_code(400);
        exit("Dados incompletos.");
    }

    $acao = (string) $_POST["acao"];
    $pokemonId = filter_var(
        $_POST["pokemon_id"],
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    if ($pokemonId === false || $pokemonId === null ||
        !in_array($acao, ["favoritar", "desfavoritar"], true)) {
        http_response_code(400);
        exit("Dados inválidos.");
    }

    if ($acao === "favoritar") {
        // Evita duplicatas mesmo se a tabela não tiver índice UNIQUE.
        $check = $conn->prepare(
            "SELECT pokemon_id FROM favoritos WHERE pokemon_id = ? LIMIT 1"
        );
        $check->bind_param("i", $pokemonId);
        $check->execute();
        $check->store_result();
        $jaExiste = $check->num_rows > 0;
        $check->close();

        if (!$jaExiste) {
            $stmt = $conn->prepare(
                "INSERT INTO favoritos (pokemon_id) VALUES (?)"
            );
            $stmt->bind_param("i", $pokemonId);
            if (!$stmt->execute()) {
                http_response_code(500);
                exit("Erro ao favoritar o Pokémon.");
            }
            $stmt->close();
        }
    } else {
        $stmt = $conn->prepare(
            "DELETE FROM favoritos WHERE pokemon_id = ?"
        );
        $stmt->bind_param("i", $pokemonId);
        if (!$stmt->execute()) {
            http_response_code(500);
            exit("Erro ao remover o favorito.");
        }
        $stmt->close();
    }

    exit("ok");
}

$resultado = $conn->query(
    "SELECT DISTINCT pokemon_id FROM favoritos ORDER BY pokemon_id ASC"
);
if (!$resultado) {
    http_response_code(500);
    exit("Erro ao carregar favoritos.");
}

$idsFavoritos = [];
while ($linha = $resultado->fetch_assoc()) {
    $id = (int) ($linha["pokemon_id"] ?? 0);
    if ($id > 0) {
        $idsFavoritos[] = $id;
    }
}

$contextoApi = stream_context_create([
    "http" => ["timeout" => 6, "ignore_errors" => true]
]);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css">
    <title>Meus favoritos | Pokédex</title>
    <style>
        /* Estilos locais desta página: não dependem de .consultor1. */
        body.pagina-favoritos {
            display: block !important;
            width: 100%;
            height: auto !important;
            min-height: 100vh;
            margin: 0;
            padding: 32px 16px;
            box-sizing: border-box;
            overflow-x: hidden;
            overflow-y: auto;
            font-family: Arial, Helvetica, sans-serif;
        }
        .favoritos-container {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
            padding: 28px;
            box-sizing: border-box;
            border: 1px solid rgba(255,255,255,.35);
            border-radius: 16px;
            background: #fff;
            color: #222;
            box-shadow: 0 18px 45px rgba(0,0,0,.3);
        }
        .favoritos-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 18px;
            margin-bottom: 26px;
        }
        .favoritos-header h1 {
            margin: 0 0 8px;
            color: #b8002d;
            font-size: clamp(26px, 4vw, 34px);
        }
        .favoritos-header p { margin: 0; color: #666; }
        .favoritos-nav {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }
        .favoritos-nav a {
            position: static !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 !important;
            padding: 11px 16px;
            border-radius: 8px;
            background: crimson;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            animation: none !important;
        }
        .favoritos-nav a:hover { margin: 0 !important; background: #a90025; }
        .lista-favoritos {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            align-items: stretch;
            gap: 18px;
            width: 100%;
        }
        .pokemon-favorito {
            display: flex;
            min-width: 0;
            flex-direction: column;
            align-items: center;
            gap: 9px;
            margin: 0;
            padding: 18px;
            box-sizing: border-box;
            border: 1px solid #e2e2e2;
            border-radius: 14px;
            background: #fafafa;
            color: #222;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,.07);
        }
        .pokemon-favorito img {
            display: block;
            width: 145px;
            max-width: 100%;
            height: 145px;
            object-fit: contain;
            image-rendering: pixelated;
        }
        .pokemon-numero { margin: 0; color: #777; font-size: 13px; }
        .pokemon-favorito h2 {
            margin: 0;
            font-size: 20px;
            text-transform: capitalize;
        }
        .pokemon-tipo { margin: 0 0 8px; color: #666; font-size: 14px; }
        .acoes-favorito {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            align-items: stretch;
            gap: 8px;
            width: 100%;
            margin-top: auto;
        }
        .acoes-favorito form { display: flex; min-width: 0; margin: 0; }
        .acoes-favorito .btn-ver,
        .acoes-favorito .btn-remover {
            position: static !important;
            inset: auto !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-width: 0;
            min-height: 42px;
            margin: 0 !important;
            padding: 9px 8px;
            box-sizing: border-box;
            border: 0;
            border-radius: 8px;
            color: #fff;
            font: 700 13px/1.25 Arial, Helvetica, sans-serif;
            text-align: center;
            text-decoration: none;
            white-space: normal;
            cursor: pointer;
            animation: none !important;
            transform: none;
        }
        .acoes-favorito .btn-ver { background: crimson; }
        .acoes-favorito .btn-ver:hover { margin: 0 !important; background: #a90025; }
        .acoes-favorito .btn-remover { background: #555; }
        .acoes-favorito .btn-remover:hover { background: #333; }
        .acoes-favorito .btn-remover:disabled { opacity: .6; cursor: wait; }
        .mensagem-vazia {
            grid-column: 1 / -1;
            margin: 0;
            padding: 28px 20px;
            border: 1px dashed #ccc;
            border-radius: 12px;
            background: #fafafa;
            color: #555;
            text-align: center;
            line-height: 1.6;
        }
        @media (max-width: 850px) {
            .lista-favoritos { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 540px) {
            body.pagina-favoritos { padding: 14px 8px; }
            .favoritos-container { padding: 18px 12px; }
            .favoritos-header { align-items: flex-start; flex-direction: column; }
            .lista-favoritos { grid-template-columns: minmax(0, 1fr); }
            .pokemon-favorito { padding: 16px; }
        }
    </style>
</head>
<body class="pagina-favoritos">
    <main class="favoritos-container">
        <header class="favoritos-header">
            <div>
                <h1>Meus favoritos</h1>
                <p>Pokémon salvos: <strong id="total-favoritos"><?= count($idsFavoritos) ?></strong></p>
            </div>
            <nav class="favoritos-nav" aria-label="Navegação">
                <a href="index.php">Início</a>
                <a href="pesquisa.php">Pesquisar Pokémon</a>
            </nav>
        </header>

        <section class="lista-favoritos" id="lista-favoritos">
            <?php if (count($idsFavoritos) === 0): ?>
                <p class="mensagem-vazia">Você ainda não tem Pokémon favoritos. Pesquise um Pokémon e clique na estrela para salvá-lo.</p>
            <?php else: ?>
                <?php foreach ($idsFavoritos as $id):
                    $urlApi = "https://pokeapi.co/api/v2/pokemon/" . $id;
                    $json = @file_get_contents($urlApi, false, $contextoApi);
                    $pokemon = $json !== false ? json_decode($json, true) : null;
                    if (!is_array($pokemon)) {
                        $pokemon = [];
                    }

                    $nomeApi = $pokemon["name"] ?? "pokemon-" . $id;
                    $nome = ucfirst($nomeApi);
                    $sprite = $pokemon["sprites"]["other"]["official-artwork"]["front_default"]
                        ?? $pokemon["sprites"]["front_default"]
                        ?? "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/" . $id . ".png";

                    $tipos = [];
                    foreach (($pokemon["types"] ?? []) as $entradaTipo) {
                        $nomeTipo = $entradaTipo["type"]["name"] ?? null;
                        if ($nomeTipo !== null) {
                            $tipos[] = ucfirst($nomeTipo);
                        }
                    }
                    $textoTipos = $tipos ? implode(" / ", $tipos) : "Tipo indisponível";
                    $linkPokemon = "pokemon.php?id=" . $id;
                ?>
                    <article class="pokemon-favorito" data-pokemon-id="<?= $id ?>">
                        <img src="<?= htmlspecialchars($sprite, ENT_QUOTES, "UTF-8") ?>"
                             alt="<?= htmlspecialchars($nome, ENT_QUOTES, "UTF-8") ?>"
                             loading="lazy">
                        <p class="pokemon-numero">#<?= $id ?></p>
                        <h2><?= htmlspecialchars($nome, ENT_QUOTES, "UTF-8") ?></h2>
                        <p class="pokemon-tipo"><?= htmlspecialchars($textoTipos, ENT_QUOTES, "UTF-8") ?></p>
                        <div class="acoes-favorito">
                            <a class="btn-ver" href="<?= htmlspecialchars($linkPokemon, ENT_QUOTES, "UTF-8") ?>">Ver Pokémon</a>
                            <form class="form-desfavoritar">
                                <input type="hidden" name="acao" value="desfavoritar">
                                <input type="hidden" name="pokemon_id" value="<?= $id ?>">
                                <button class="btn-remover" type="submit">★ Remover</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <script>
        document.querySelectorAll(".form-desfavoritar").forEach(form => {
            form.addEventListener("submit", async event => {
                event.preventDefault();
                const botao = form.querySelector("button");
                const card = form.closest(".pokemon-favorito");
                botao.disabled = true;

                try {
                    const resposta = await fetch("favoritos.php", {
                        method: "POST",
                        body: new FormData(form)
                    });
                    const texto = await resposta.text();
                    if (!resposta.ok || texto.trim() !== "ok") {
                        throw new Error(texto || "Não foi possível remover o favorito.");
                    }

                    card.remove();
                    const total = document.querySelectorAll(".pokemon-favorito").length;
                    document.getElementById("total-favoritos").textContent = total;

                    if (total === 0) {
                        const lista = document.getElementById("lista-favoritos");
                        lista.innerHTML = "";
                        const mensagem = document.createElement("p");
                        mensagem.className = "mensagem-vazia";
                        mensagem.textContent = "Você ainda não tem Pokémon favoritos. Pesquise um Pokémon e clique na estrela para salvá-lo.";
                        lista.appendChild(mensagem);
                    }
                } catch (erro) {
                    alert(erro.message);
                    botao.disabled = false;
                }
            });
        });
    </script>
</body>
</html>
