<?php

return [
    /** Запросы к бирже по АПИ */
    'get-data-exchange'    => 'exchange:dataByCurl',
    'update-data-exchange' => 'exchangeApi:updateData',

    /** Страница инструмента */
    'share/([0-9_a-zA-Z]+)/([a-z]+)' => 'stock:page:$1:$2',
    'share/([0-9_a-zA-Z]+)'          => 'stock:page:$1',

    /** Страница акций */
    'modal-info'          => 'stock:getModalAjax',
    'idea'                => 'stock:ideaCheckAjax',
    'ajax-add-portfolio'  => 'stock:addPortfolioAjax',
    'update-data'         => 'stock:updateDataAjax',
    'get/([0-9_a-zA-Z]+)' => 'stock:index:$1',
    'shares'              => 'stock:index',

    /** Портфель ценных бумаг */
    'portfolio-remove-assets' => 'portfolio:removeAssetsFromPortfolioAjax',
    'portfolio/([a-z]+)'      => 'portfolio:index:$1',
    'portfolio'               => 'portfolio:index',

    'registration'  => 'user:regist',

    /** */
    '/' => 'home:index',
    ''  => 'home:index'
];