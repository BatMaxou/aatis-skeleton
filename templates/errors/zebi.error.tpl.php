<?php $title = 'Error '.$code; ?>

<!DOCTYPE html>
<html lang="fr">

<?php echo $renderer->render(
    $templatesFolderPath.'/includes/header.tpl.php',
    [
        'title' => $title,
    ]
); ?>

<body>
    <h1>Error <?php echo $code; ?> :</h1>
    <p><?php echo $message; ?></p>
</body>

</html>
