<!DOCTYPE html>
<html lang="fr">

<?php echo $renderer->render(
    $templatesFolderPath.'/includes/header.tpl.php',
    [
        'title' => $title,
    ]
); ?>

<body>
    <h1><?php echo $title; ?></h1>
</body>

</html>
