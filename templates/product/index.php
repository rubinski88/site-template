<?php

/** @var list<array{name: string, description: ?string}> $products */
/** @var int $total */
?>
<p>
    total records <?= e($total) ?>
</p>

<?php foreach ($products as $product): ?>
    <h2><a href="/products/<?= $product['id'] ?>">
            <?= e($product['name']) ?>
        </a></h2>
    <p><?= e($product['description']) ?></p>
<?php endforeach; ?>