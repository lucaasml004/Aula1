<?php
// ==========================================
// FICHEIRO CENTRAL DE CONEXÃO À BASE DE DADOS
// ==========================================

$host = 'localhost';   // O endereço do servidor (o XAMPP usa sempre localhost)
$db   = 'ipca_gestao'; // Nome da nossa base de dados criada no PhpMyAdmin
$user = 'root';        // O utilizador padrão do MySQL no XAMPP é 'root'
$pass = '';            // A senha padrão no XAMPP costuma ser vazia
$charset = 'utf8mb4';  // Muito Importante: Permite gravar textos com acentos (ç, ã) e Emojis sem dar erro de carateres estranhos

// DSN (Data Source Name): É o formato de ligação que o PHP exige
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Opções de segurança e conveniência do PDO (PHP Data Objects)
$options = [
    // Se algo correr mal, o PHP vai "disparar" um Alerta de Erro grave visível para tratarmos o bug
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Devolve-nos as tabelas estruturadas com nomes. Ex: usar $tabela['nome_aluno'] em vez de $tabela[3]
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Reforça a proteção contra Injeções de SQL obrigando o servidor a preparar as rotinas
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     // MAGIA ACONTECE AQUI: A variável $pdo passa a ser o nosso "Cabo elétrico" ligado à Base de Dados!
     // Sempre que noutro ficheiro precisarmos da BD, basta importar e usar o $pdo.
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Proteção contra desastre: Se o utilizador esquecer de ligar o botão do MYSQL no XAMPP, o código avisa o erro aqui!
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>