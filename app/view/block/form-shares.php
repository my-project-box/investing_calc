<div class="accordion">
    <div class="accordion-item">
        
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">Фильтры</button>
        </h2>

        <div id="collapseOne" class="accordion-collapse collapse show">
            <div class="accordion-body">
                
            <div class="filter">
                <form id="filter" action method="get">
                    <fieldset>
                        
                        <div>
                            <legend>Портфель</legend>

                            <select id="portfolio" class="form-select" name="portfolio">
                                <option value="0"></option>
                                <option value="all" <?= isset ($filter['portfolio_id']) && $filter['portfolio_id'] == 'all' ? "selected" : '' ?>>Кроме моих</option>

                                <?php if ( !empty ($portfolios) ) : ?>
                                    <?php foreach ($portfolios as $portfolio) : ?>
                                        <option value="<?= $portfolio['id'] ?>" <?= isset ($filter['portfolio_id']) && $filter['portfolio_id'] == $portfolio['id'] ? "selected" : '' ?>><?= $portfolio['name'] ?></option>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </select>
                        </div>

                        <div class="mt-3">
                            <legend>Уровень листинга</legend>

                            <select id="listlevel" class="form-select" name="listlevel">
                                <option value="0" <?= isset ($filter['listlevel']) && $filter['listlevel'] == 0 ? "selected" : '' ?>>Все</option>
                                <option value="1" <?= isset ($filter['listlevel']) && $filter['listlevel'] == 1 ? "selected" : '' ?>>1</option>
                                <option value="2" <?= isset ($filter['listlevel']) && $filter['listlevel'] == 2 ? "selected" : '' ?>>2</option>
                                <option value="3" <?= isset ($filter['listlevel']) && $filter['listlevel'] == 3 ? "selected" : '' ?>>3</option>
                            </select>
                        </div>
                        
                        <div class="mt-3">
                            <legend>Показать выбранные</legend>

                            <div class="form-check form-check-inline form-switch">
                                <input class="form-check-input" type="checkbox" name="idea" value="1" <?= isset ($filter['idea']) && $filter['idea'] == 1 ? "checked" : '' ?>>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Интервал</legend>
                        <input type="date" name="date_from" value="<?= $filter['date_from'] ?>">
                        <input type="date" name="date_to" value="<?= $filter['date_to'] ?>">
                    </fieldset>
                </form>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit" value="" form="filter">Получить данные</button>
                </div>
                
            </div> <!-- :filter -->

            </div>
        </div>

    </div>
</div>