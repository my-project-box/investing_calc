<?php require_once 'app/view/block/header.php'; ?>

<section  id="stock" class="container-fluid">
    <h1 class="mt-5"><?= $secname ?> / <?= $secid ?></h1>
    <div class="row mt-5">

        <div class="col-6">
            <h2>Покупка</h2>

            <form class="needs-validation" action method="get" novalidate>
                <div class="row mt-3">
                    <div class="col-4">
                        <label for="quantity" class="form-label">Количество</label>
                        <input id="quantity" class="form-control" type="text" name="quantity" value="" required>
                    </div>

                    <div class="col-4">
                        <label for="price" class="form-label">Цена</label>
                        <input id="price" class="form-control" type="text" name="price" value="" required>
                    </div>

                    <div class="col-4">
                        <label for="date" class="form-label">Дата</label>
                        <input id="date" class="form-control" type="date" name="date" value="" required>
                    </div>

                    <div class="form-text">Добавить информацию о покупке</div>
                </div>

                <div class="col-12 mt-3 mb-3">
                    <button type="submit" class="btn btn-primary" name="add" value="1">Добавить</button>
                </div>
            </form>

            <div class="scroll-table">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Кол-во</th>
                            <th>Цена покупки</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ( !empty ($assets) ) : ?>
                        <?php foreach ( $assets as $asset) : ?>
                        <tr>
                            <td><?= $asset['date'] ?></td>
                            <td><?= $asset['quantity'] ?></td>
                            <td><?= $asset['price'] ?></td>
                            <td><a href="/<?= $url ?>/delete?date=<?= $asset['date'] ?>"><i class="fa fa-trash-o"></i></a></td>
                        </tr>
                        <?php endforeach ?>
                    <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-6"><h2>Дивиденды</h2></div>
    </div>
</section>

<script>
    (function () {
    'use strict'

    // Получите все формы, к которым мы хотим применить пользовательские стили проверки Bootstrap
    var forms = document.querySelectorAll('.needs-validation')

        // Зацикливайтесь на них и предотвращайте отправку
        Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
    })()
</script>

<?php require_once 'app/view/block/footer.php'; ?>