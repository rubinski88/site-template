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
    <main>
        <?php require $templatePath; ?>
    </main>
</body>

</html>