<?php 

$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "pokedex";

$conn = new mysqli($servidor, $usuario, $senha, $banco);

if ($conn->connect_error){
    die("Erro na conexao" . $conn->connect_error);
}

?>