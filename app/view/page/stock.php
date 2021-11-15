<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <!-- CSS -->
    <link href="app/view/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>


</head>
<body>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                            <div class="security__list">
                                <div class="security__head">
                                    <div><span>Наименование</span></div>
                                    <div><span>Тикет</span></div>
                                    <div><span>Тип акции</span></div>
                                    <div><span>Кол-во в лоте</span></div>
                                    <div><span>Выпущено</span></div>
                                    <div><span>Валюта</span></div>
                                    <div><span>Цена рыночная</span></div>
                                    <div><span>Капитализация</span></div>
                                    <div><span>Цена реальная</span></div>
                                    <div><span>Дивидендная доходность, % / ед</span></div>
                                </div>
                                <?php foreach ($securities as $security) : ?>
                                <div class="security__body">
                                    <div><?= $security['secname'] ?></div>
                                    <div><?= $security['secid'] ?></div>
                                    <div><?= $security['type'] ?></div>
                                    <div><?= $security['lotsize'] ?></div>
                                    <div><?= $security['issuesize'] ?></div>
                                    <div><?= $security['faceunit'] ?></div>
                                    <div><?= $security['last'] ?></div>
                                    <div><input type="text" name="text" value=""></div>
                                    <div></div>
                                    <div><?= $security['dividend_yield_percent'] ?>% 
                                    <?php if (!empty ($security['dividends'])) : ?> / <?= $security['dividends']['average_value_total'] ?><?php endif ?>
                                    </div>
                                </div>
                                <?php endforeach ?>
                            </div>

            </div>
        </div>
    </div>

<script>
    window.window.addEventListener('scroll', function(e) {
        console.log(window.scrollY)

        if (window.scrollY > 100) {
            document.querySelector('.security__head').classList.add('security__head-fixed')
        } else {
            document.querySelector('.security__head').classList.remove('security__head-fixed')
        }
    });
</script>

</body>
</html>