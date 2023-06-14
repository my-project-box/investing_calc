<?php require_once 'app/view/block/header.php'; ?>

<section id="portfolio" class="container-fluid">

    <h1 class="mt-5"><?= $title_page ?></h1>

    <form class="row mt-5" action method="post">
        <div class="col-auto">
            <input class="form-control" type="text" name="name" value="" placeholder="Имя портфеля">
        </div>

        <div class="col-auto">
            <button class="btn btn-success" type="submit">Добавить</button>
        </div>
        <input type="hidden" name="user_id" value="<?= $user_id ?>">
    </form>

    <div class="row row-cols-2 mt-5">
        <?php if (!$portfolios) : ?>
            <div class="col-11"><?= $text_info ?></div>
        <?php endif ?>
 
        <?php foreach ($portfolios as $portfolio) : ?>
            <div class="portfolio col mb-3 accordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="flush-heading-<?= $portfolio['id'] ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-<?= $portfolio['id'] ?>">
                            <span class="w-50"><?= empty ($portfolio['name']) ? $portfolio_name_default . $portfolio['id'] : $portfolio['name'] ?></span>
                        </button>
                    </h2>
                    <div id="flush-collapse-<?= $portfolio['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            <?php if ( !empty ( $assets[$portfolio['id']] ) ) : ?>
                                <div class="donut_single" style="width: 100%;"></div>
                                <div class="scroll-table mt-5">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Наименование</th>
                                                <th>Тикер</th>
                                                <th>Кол-во</th>
                                                <th>Сумма инвестиций</th>
                                                <th>Текущая цена</th>
                                                <th>Текущая доходность</th>
                                                <th colspan="2"></th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php foreach ( $assets[$portfolio['id']] as $asset) : ?>
                                            <tr>
                                                <td><a href="/share/<?= $asset['secid'] ?>"><?= $asset['secname'] ?></a></td>
                                                <td class="tiker"><?= $asset['secid'] ?></td>
                                                <td class="quantity"><?= !empty ( $asset['quantity'] ) ? $asset['quantity'] : 0 ?></td>
                                                <td class="total_buy_price"><?= !empty ( $asset['total_buy_price'] ) ? $asset['total_buy_price'] : 0.00 ?></td>
                                                <td class="price"><?= $asset['price'] ?></td>
                                                <td class="current_yield"><?= !empty ( $asset['current_yield'] ) ? $asset['current_yield'] : 0.00 ?>%</td>
                                                <td class="edit" data-tiker="<?= $asset['secid'] ?>" data-portfolio="<?= $portfolio['id'] ?>"><i class="fa fa-edit"></i></td>
                                                <td class="trash" data-tiker="<?= $asset['secid'] ?>" data-portfolio="<?= $portfolio['id'] ?>"><i class="fa fa-trash-o"></i></td>
                                            </tr>
                                            <?php endforeach ?>
                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <td colspan="3">Итого:</td>
                                                <td class="total_price text-start" >0</td>
                                                <td></td>
                                                <td class="total_yield text-start">0</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            <?php else : ?>
                                <div><?= $portfolio_info_default ?></div>
                            <?php endif ?>
                        </div>

                        <div class="d-flex justify-content-end pb-3 pe-3">
                            <!--<button class="btn btn-primary">Редактировать имя портфеля</button>-->
                            <a class="btn btn-danger" href="/portfolio/delete?id=<?= $portfolio['id'] ?>">Удалить портфель</a>
                        </div>
                        
                    </div>
                </div>
            </div>
        <?php endforeach ?>

    </div>
    
</section>

<?php require_once 'app/view/block/footer.php'; ?>