<?php

/** @var string $templatePath */
/** @var string $title */

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= e($title) ?></title>
</head>

<body>
    <?php require ROOT_PATH . 'templates/_nav.php'; ?>

    <main>
        <?php require $templatePath; ?>
    </main>
</body>

</html>