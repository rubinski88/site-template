<?php

/** @var array $product */
?>

<h2>
    Edit product
</h2>
<form action="/products/<?= e($product['id']) ?>" method="post">
    <input type="hidden" name="_method" value="PATCH">
    <?php require '_form.php' ?>
</form>
<p>
    <a href="/products/<?= e($product['id']) ?>">
        Back to product
    </a>
</p>
