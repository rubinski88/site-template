<?php

/** @var array $product */

?>

<h2>
    <?= $product['name'] ?>
</h2>
<p>
    <?= e($product['description']) ?>
</p>

<p>
    <a href="/products/<?= e($product['id']) ?>/edit">
        Edit product
    </a>
</p>

<form method="post" action="/products/<?= e($product['id']) ?>">
    <input type="hidden" name="_method" value="DELETE">
    <input type="submit" value="Delete product">
</form>
