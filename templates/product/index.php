<?php

/** @var list<array{name: string, description: ?string}> $products */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Products</title>
</head>

<body>
    <?php foreach ($products as $product): ?>
        <h2><?= e($product['name']) ?></h2>
        <p><?= e($product['description']) ?></p>
    <?php endforeach; ?>
</body>

</html>