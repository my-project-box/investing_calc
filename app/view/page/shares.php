<?php require_once 'app/view/block/header.php'; ?>

<section  id="stock" class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1>Акции</h1>
            <div class="content">
                <?php require_once 'app/view/block/form-shares.php'; ?>

                <table class="list">
                    <thead>
                        <tr>
                            <th rowspan="2"><span>Наименование</span></th>
                            <th rowspan="2"><span>Идея</span></th>
                            <th rowspan="2"><span>Тикер</span></th>
                            <th rowspan="2"><span>Тип акции</span></th>
                            <th rowspan="2"><span>Уровень листинга</span></th>
                            <th rowspan="2"><span>Лот</span></th>
                            <th rowspan="2"><span>Выпущено</span></th>
                            <th rowspan="2"><span>Валюта</span></th>
                            <th rowspan="2"><span>Активы</span></th>
                            <th colspan="3"><span>Цена</span></th>
                            <th colspan="3"><span>Дивиденды</span></th>
                        </tr>

                        <tr>
                            <td><span>Рыночная</span></td>
                            <td><span>Фактическая</span></td>
                            <td><span>Разница, %</span></td>
                            <td><span>Доходность, %</span></td>
                            <td><span>Регулярность</span></td>
                            <td><span>Всего лет</span></td>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($securities as $security) : ?>
                        <tr>
                            <td class="name"><span><?= $security['secname'] ?></span></td>
                            <td><span><input type="checkbox" name="idea" <?= isset ($filter['idea']) && $filter['idea'] == 1 || $security['idea'] == 1 ? "checked" : '' ?>  data-secid="<?= $security['secid'] ?>"></span></td>
                            <td class="tiker"><span><?= $security['secid'] ?></span></td>
                            <td><span><?= $security['type'] ?></span></td>
                            <td><span><?= $security['listlevel'] ?></span></td>
                            <td><span><?= $security['lotsize'] ?></span></td>
                            <td class="issuesize"><span><?= $security['issuesize'] ?></span></td>
                            <td><span><?= $security['faceunit'] ?></span></td>
                            <td class="assets"><span><?= $security['assets'] ?></span></td>

                            <td><?= $security['last'] ?></td>
                            <td class="actual_price"><?= $security['actual_price'] ?></td>
                            <td><?= $security['percent_of_price_market'] ?></td>

                            <td><?= $security['dividends_yield_years'] ?>%</td>
                            <td><?= $security['dividends_years_checked'] ?></td>
                            <td><?= $security['dividends_number_years_pay'] ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</section>

<script>
    if (window.pageYOffset > 0) {
        document.querySelector('.list thead').classList.add('thead-fixed')
    }

    window.addEventListener('scroll', function(e) {
        //console.log(window)

        if (window.scrollY > 100) {
            document.querySelector('.list thead').classList.add('thead-fixed')
        } else {
            document.querySelector('.list thead').classList.remove('thead-fixed')
        }
    });

    
</script>

<?php require_once 'app/view/block/footer.php'; ?>