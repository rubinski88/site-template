<?php if (isset($errors['name'])): ?>
    <p>
        <?= $errors['name'] ?>
    </p>
<?php endif; ?>
<p>
    Name <input type="text" name="name" value="<?= $product['name'] ?? '' ?>">
</p>
<p>
    Description <textarea name="description"><?= $product['description'] ?? '' ?></textarea>
</p>
<input type="submit" value="Save">