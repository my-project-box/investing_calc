<div class="modal fade" id="staticBackdrop" data-bs-backdrop="true" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" data-userid="<?= $user_id ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= $secname ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <label for="assets" class="form-label">Капитализация:</label>

                <?php if ( isset ($assets) ) : ?>
                    <input id="assets"  class="form-control"  type="text" name="assets" value="<?= $assets ?>" placeholder="Нет данных">
                <?php else : ?>
                    <input id="assets"  class="form-control"  type="text" name="assets" value="" placeholder="Нет данных">
                <?php endif ?>

                <label for="portfolio" class="form-label mt-3">Портфели:</label>

                <?php if ( empty ( $portfolios ) ) : ?>
                    <div>Нет портфелей. <a href="/portfolio">Создать</a></div>
                <?php else : ?>
                    <select id="portfolio" class="form-select" name="portfolio">
                    <option value=""></option>
                        <?php foreach ( $portfolios as $portf ) : ?>
                            <option value="<?= $portf['id'] ?>" <?= $portf['id'] == $portfolio_id ? 'selected' : '' ?>><?= $portf['name'] ?></option>
                        <?php endforeach ?>
                    </select>
                <?php endif ?>

            </div>

            <div class="modal-footer">
                <button id="save" type="button" class="btn btn-primary">Сохранить</button>
                <button id="close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>