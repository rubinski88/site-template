<?php

/** @var list<array{name: string, description: ?string}> $products */
?>


    <?php foreach ($products as $product): ?>
        <h2><?= e($product['name']) ?></h2>
        <p><?= e($product['description']) ?></p>
    <?php endforeach; ?>
