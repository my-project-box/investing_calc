<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>

    <!-- CSS -->
    <link href="../app/view/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script src="https://use.fontawesome.com/aa23a6ba49.js"></script>
    <script src="../app/view/js/main.js"></script>


</head>
<body>
    <header id="header" class="container-fluid">
        <div class="row">
            <div class="col-1 logo">
                <div class="">Аккаунт</div>
            </div>
            <div class="col-11"></div>
        </div>
    </header>

<main class="container-fluid">
    <div class="row">
        <div class="col-1 column_left">
            <?php require_once 'app/view/block/sidebar.php'; ?>
        </div>
        <div class="col-11">