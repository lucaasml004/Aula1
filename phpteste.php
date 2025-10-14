<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Simulação de Processamento de Salários</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            max-width: 400px;
            margin: 0 auto;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input[type="number"], input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        input[type="submit"] {
            margin-top: 20px;
            background: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
        }
        .resultado {
            background: #e9ffe9;
            border: 1px solid #b3ffb3;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<h1>Simulação de Processamento de Salário</h1>

<form method="post">
    <label>MIGAY</label>
    <input type="text" name="nome" required>

    <label>Salário Bruto (R$):</label>
    <input type="number" name="salario_bruto" step="0.01" required>

    <input type="submit" value="Calcular Salário">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $salario_bruto = floatval($_POST["salario_bruto"]);

    // Cálculo do INSS (alíquota simplificada)
    if ($salario_bruto <= 1412.00) {
        $inss = $salario_bruto * 0.075;
    } elseif ($salario_bruto <= 2666.68) {
        $inss = $salario_bruto * 0.09;
    } elseif ($salario_bruto <= 4000.03) {
        $inss = $salario_bruto * 0.12;
    } else {
        $inss = $salario_bruto * 0.14;
    }

    // Base de cálculo para IRRF
    $base_irrf = $salario_bruto - $inss;

    // Cálculo do IRRF (faixas simplificadas)
    if ($base_irrf <= 2259.20) {
        $irrf = 0;
    } elseif ($base_irrf <= 2826.65) {
        $irrf = $base_irrf * 0.075 - 169.44;
    } elseif ($base_irrf <= 3751.05) {
        $irrf = $base_irrf * 0.15 - 381.44;
    } elseif ($base_irrf <= 4664.68) {
        $irrf = $base_irrf * 0.225 - 662.77;
    } else {
        $irrf = $base_irrf * 0.275 - 896.00;
    }

    if ($irrf < 0) $irrf = 0;

    // Salário líquido
    $salario_liquido = $salario_bruto - $inss - $irrf;
    ?>

    <div class="resultado">
        <h3>Resultado da Simulação</h3>
        <p><strong>Funcionário:</strong> <?= htmlspecialchars($nome) ?></p>
        <p><strong>Salário Bruto:</strong> R$ <?= number_format($salario_bruto, 2, ',', '.') ?></p>
        <p><strong>Desconto INSS:</strong> R$ <?= number_format($inss, 2, ',', '.') ?></p>
        <p><strong>Desconto IRRF:</strong> R$ <?= number_format($irrf, 2, ',', '.') ?></p>
        <hr>
        <p><strong>Salário Líquido:</strong> R$ <?= number_format($salario_liquido, 2, ',', '.') ?></p>
    </div>
<?php } ?>

</body>
</html>
