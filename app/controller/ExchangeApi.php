<?php

// Класс работы с Московской фондовой биржей

namespace controller;

use 
    engine\Controller,
    model\Modelstock;

class ExchangeApi extends Controller 
{
    private string $params;
    private string $securities;
    private string $security_description;
    private string $dividends;
    private string $history;

    
    /**
     * Настройки к API московской биржи
     * 
     */
    private array $options_api_moex = [
        'params' => ['iss.json' => 'extended', 'iss.meta' => 'off'],

        'securities' => [
            'iss.only'           => 'securities,marketdata',
            'securities.columns' => 'SECID,BOARDID,SHORTNAME,SECNAME,LOTSIZE,ISIN,REGNUMBER,ISSUESIZE,FACEUNIT,LISTLEVEL',
            'marketdata.columns' => 'SECID,BOARDID,LAST'
        ],

        'securities_secid' => [
            'iss.only'           => 'securities',
            'securities.columns' => 'SECID,BOARDID'
        ],

        'securities_last_price' => [
            'iss.only'           => 'marketdata',
            'marketdata.columns' => 'SECID,BOARDID,LAST'
        ],

        'security_description' => [
            'iss.only'            => 'description',
            'description.columns' => 'name,title,value'
        ],

        'dividends' => [
            'dividends.columns' => 'secid,registryclosedate,value,currencyid'
        ],

        'history' => [
            'history.columns' => 'SECID,TRADEDATE,LEGALCLOSEPRICE'
        ]
    ];

    

    /**
     *  Конструктор
     * 
     */
    public function __construct ($registry) 
    {
        parent::__construct($registry);

    }



    /**
     * Обновление данных от московской бирже
     * Получаем ссылки для API
     * 
     */
    public function updateData () 
    {
        $pre_data  = [];

        // Подключаем модели
        $this->load->model('', 'Stock');
        $this->load->model('', 'ExchangeApi');

        // Таблица инструментов 
        $params_api = $this->getStringOptions('params', 'securities');
        $result_add_boards_link_api = $this->addBoardsTradeLinkApiToDB('UpdateData', $params_api);
        if ($result_add_boards_link_api == 404) return false;
        $result_get_boards = $this->getBoardsTradeDataOnApi('UpdateData', 50);

        foreach ( $result_get_boards as $boards )
            foreach ( $boards['securities'] as $val )
                $securities[$val['SECID']] = $val;


        foreach ( $result_get_boards as $boards )
            foreach ( $boards['marketdata'] as $val )
                $securities[$val['SECID']] += $val;

        //dd($securities);
        // Дополнительное описание инструмента
        $params_api = $this->getStringOptions('params', 'security_description');
        $result_add_security_description_link_api = $this->addSecurityDescriptionLinkApiToDB('UpdateData', $params_api);
        if ($result_add_security_description_link_api == 404) return false;
        $result_get_security_description = $this->getSecurityDescriptionDataOnApi('UpdateData', 50);
        //dd($result_get_security_description);
        foreach ( $result_get_security_description as $val )
        {
            $security_description[$val['SECID']]['ISQUALIFIEDINVESTORS'] = $val['ISQUALIFIEDINVESTORS'];
            $security_description[$val['SECID']]['TYPENAME']             = $val['TYPENAME'];
        }
                

        d($security_description);
        // Дивиденды
        /*$params_api = $this->getStringOptions('params', 'dividends');
        $result_add_dividends_link_api = $this->addDividendsLinkApiToDB('UpdateData', $params_api);
        if ($result_add_dividends_link_api == 404) return false;
        $result_get_dividends = $this->getDividendsDataOnApi('UpdateData', 50);
        dd($result_get_dividends);*/

        // История цены закрытия дивиденда
        /*$params_api = $this->getStringOptions('params', 'history');
        $result_add_close_price_dividends_link_api = $this->addClosePriceDividendsLinkApiToDB('UpdateData', $params_api);
        if ($result_add_close_price_dividends_link_api == 404) return false;
        $result_get_close_price_dividends = $this->getClosePriceDividendsDataOnApi('UpdateData', 50);
        dd($result_get_close_price_dividends);*/
        
        dd('end');
            
    } 



    /**
     * Добавляем в базу ссылку для АПИ таблиц инструментов по режиму торгов
     * TQBR- для акций
     */
    private function addBoardsTradeLinkApiToDB(string $process, string $params_api)
    {
        if ( empty($process) ) return 404;

        $checker = $this->checkerStatusApi( $process, 'BoardsTrade', 'get' );
        if ( isset($checker) && $checker['status'] == 1 ) return false;

        $url_api = [
            'tqbr' => 'https://iss.moex.com/iss/engines/stock/markets/shares/boards/TQBR/securities.json?' . $params_api,
        ];

        $data = [
            'process' => $process,
            'type'    => 'BoardsTrade',
            'source'  => 'moex',
            'api'     => $url_api
        ];

        // Записываем в базу данных
        return $this->model_exchange_api->insertQueryForAPI ($data);
    }



    /**
     * Получаем по АПИ таблицы инструментов по режиму торгов
     * TQBR- для акций
     */
    private function getBoardsTradeDataOnApi(string $process, int $limit)
    {
        $result_get = $this->model_exchange_api->getQueryForAPI(['type' => 'BoardsTrade', 'process' => $process], $limit);
        if ( empty($result_get) ) return 404;

        $curl = $this->curl->multiThreads ($result_get);
        if ( empty($curl['result']) ) return 404;
        
        foreach ($curl['result'] as $key => $val) 
        {
            if ($curl['info'][$key]['http_code'] == 200)
                $data[$key] = json_decode ($val, true)[1];
        }

        $this->model_exchange_api->deleteQueryForAPIOnCurlData($data);

        $result_get_checker = $this->model_exchange_api->getQueryForAPI(['type' => 'BoardsTrade', 'process' => $process], 1);
        
        if ( empty($result_get_checker) )
            $this->checkerStatusApi($process, 'BoardsTrade', 'set', 0);
        else $this->checkerStatusApi($process, 'BoardsTrade', 'set', 1);

        return $data;
    }



    /**
     * Добавляем в базу ссылку для АПИ дивидендов
     * 
     */
    private function addDividendsLinkApiToDB(string $process, string $params_api)
    {
        $checker = $this->checkerStatusApi( $process, 'Dividends', 'get' );
        if ( isset($checker) && $checker['status'] == 1 ) return false;
        
        $add_boards = $this->addBoardsTradeLinkApiToDB('Dividends', $this->getStringOptions('params', 'securities_secid'));
        if ( $add_boards == 404 ) return false;
        
        $get_boards = $this->getBoardsTradeDataOnApi('Dividends', 1);
        if ( $get_boards == 404 ) return false;

        $date_period1 = new \DateTime('2000-01-01');
        $date_period2 = new \DateTime(date('Y-m-d'));

        $period1 = $date_period1->format ('U');
        $period2 = $date_period2->format ('U');

        // Собираем урлы
        // Шаблон Московской биржы: https://iss.moex.com/iss/securities/[SECID]/dividends.json?
        // Шаблон Yahoo: https://query1.finance.yahoo.com/v8/finance/chart/AGRO.ME?symbol=AGRO.ME&period1=946684800&period2=1609459199&interval=1mo&includePrePost=true&events=div,split

        foreach($get_boards as $id)
        {
            foreach ($id['securities'] as $security)
            {
                $url_api['moex'][]     = 'https://iss.moex.com/iss/securities/' . $security['SECID'] . '/dividends.json?' . $params_api;
                $url_api['yahoo_me'][] = 'https://query1.finance.yahoo.com/v8/finance/chart/'. $security['SECID'].'.ME?symbol='. $security['SECID'] .'.ME&period1='. $period1 .'&period2='. $period2 .'&interval=1mo&includePrePost=true&events=div,split';
                $url_api['yahoo_il'][] = 'https://query1.finance.yahoo.com/v8/finance/chart/'. $security['SECID'] .'.IL?symbol='. $security['SECID'] .'.IL&period1='. $period1 .'&period2='. $period2 .'&interval=1mo&includePrePost=true&events=div,split';
            }
        }
        
        
        $data = [
            'process' => $process,
            'type'    => 'Dividends',
            'source'  => 'moex',
            'api'     => $url_api
        ];

        // Записываем в базу данных
        return $this->model_exchange_api->insertQueryForAPI ($data);
    }



    /**
     * Получаем данные по АПИ для дивидендов
     * 
     */
    private function getDividendsDataOnApi(string $process, int $limit)
    {
        $data_del = [];

        // Московская биржа
        $result_get_moex = $this->model_exchange_api->getQueryForAPI(['type' => 'Dividends', 'process' => $process, 'source' => 'moex'], $limit);
        if ( empty($result_get_moex) ) return 404;

        $curl = $this->curl->multiThreads ($result_get_moex);
        if( empty($curl['result']) ) return 404;
        
        foreach ($curl['result'] as $key => $val) 
        {
            $json = json_decode ($val, true)[1];
            if ( empty ($json['dividends']) ) continue;
            
            foreach ($json['dividends'] as $key_json => $dividents) 
            {
                $date = new \DateTime($dividents['registryclosedate']);
                $json['dividends'][$key_json]['month_close_date'] = $date->format('Y-m-00');
                $json['dividends'][$key_json]['date_close_price'] = $this->getDateClosePrice( $dividents['registryclosedate'] );
                $json['dividends'][$key_json]['date_time'] = date('Y-m-d H:i:s');
            }

            $data[$key] = $json['dividends'];
            
        }
        
        $data_del += $curl['info'];
        
        // Yahoo
        $result_get['yahoo_dividends_query'] = [];
        $result_get['yahoo_dividends_query'] += $this->model_exchange_api->getQueryForAPI(['type' => 'Dividends', 'process' => $process, 'source' => 'yahoo_il'], $limit);
        $result_get['yahoo_dividends_query'] += $this->model_exchange_api->getQueryForAPI(['type' => 'Dividends', 'process' => $process, 'source' => 'yahoo_me'], $limit);
        
        $curl = $this->curl->multiThreads ($result_get['yahoo_dividends_query']);
    
        $date = new \DateTime();
        $date->setTimezone(new \DateTimeZone('Europe/Moscow'));

        foreach ($curl['result'] as $key => $json) 
        {
            $json_data = json_decode ($json, true);

            if (empty($json_data['chart']['result'])) continue;

            $chart = $json_data['chart']['result'][0];
            
            if (!isset ($chart['events']) || !isset($chart['events']['dividends'])) continue;

            foreach ($chart['events']['dividends'] as $div_yahoo) 
            {
                $symbol = preg_replace('~\.[A-Z]+$~', '$1', $chart['meta']['symbol']);

                $data[$key][] = [
                    'secid'             => $symbol,
                    'registryclosedate' => $date->setTimestamp($div_yahoo['date'])/*->modify('+1 day')*/->format('Y-m-d'),
                    'value'             => $div_yahoo['amount'],
                    'currencyid'        => $chart['meta']['currency'],
                    'month_close_date'  => $date->setTimestamp($div_yahoo['date'])->format('Y-m-00'),
                    'date_close_price'  => $this->getDateClosePrice( $date->setTimestamp( $div_yahoo['date'] )->format('Y-m-d') ),
                    'date_time'         => date('Y-m-d H:i:s')
                ];
            }
        }
        
        $data_del += $curl['info'];
        //$result_add = $this->model_exchange_api->addDividends ($result['dividends']);

        if( empty($data) ) return 404;
        
        $this->model_exchange_api->deleteQueryForAPIOnCurlData($data_del);

        $result_get_checker = $this->model_exchange_api->getQueryForAPI(['type' => 'Dividends', 'process' => $process], 1);
        
        if ( empty($result_get_checker) )
            $this->checkerStatusApi($process, 'Dividends', 'set', 0);
        else $this->checkerStatusApi($process, 'Dividends', 'set', 1);

        return $data;
    }



    /**
     * Формируем ссылку к АПИ мос. биржы и записываем ее в базу
     * 
     */
    private function addSecurityDescriptionLinkApiToDB(string $process, string $params_api)
    {
        $checker = $this->checkerStatusApi( $process, 'SecurityDescription', 'get' );
        if ( isset($checker) && $checker['status'] == 1 ) return false;
        
        $add_boards = $this->addBoardsTradeLinkApiToDB('SecurityDescription', $this->getStringOptions('params', 'securities_secid'));
        if ( $add_boards == 404 ) return false;

        $get_boards = $this->getBoardsTradeDataOnApi('SecurityDescription', 50);
        if ( $get_boards == 404 ) return false;
        
        foreach($get_boards as $id)
        {
            foreach ($id['securities'] as $security) 
                $url_api[$security['SECID']] = 'https://iss.moex.com/iss/securities/' . $security['SECID'] . '.json?' . $params_api;
        }

        $data = [
            'process' => $process,
            'type'    => 'SecurityDescription',
            'source'  => 'moex',
            'api'     => $url_api // 246 строк
        ];
        
        // Записываем в базу данных
        return $this->model_exchange_api->insertQueryForAPI ($data);
    }



    /**
     * Получаем подробное описание инструмента по АПИ московской бирже
     * 
     */
    private function getSecurityDescriptionDataOnApi(string $process, int $limit)
    {
        
        $result_get = $this->model_exchange_api->getQueryForAPI( ['type' => 'SecurityDescription', 'process' => $process], $limit );
        if ( empty($result_get) ) return 404;
       
        $curl = $this->curl->multiThreads( $result_get );
        if ( empty($curl['result']) ) return 404;

        $data_del = [];

        foreach($curl['result'] as $key => $val)
        {
            $description = json_decode($val, true)[1];

            foreach($description as $elems)
            {
                foreach($elems as $el)
                {
                    $name = $el['name'];
                    $value = $el['value'];
                    $arr[$name] = $value;
                }
                $data[] = $arr;
            }
        }
        
        if( !empty($data) )
        {
            $this->model_exchange_api->deleteQueryForAPIOnCurlData($curl['info']);

            $result_get_checker = $this->model_exchange_api->getQueryForAPI(['type' => 'SecurityDescription', 'process' => $process], 1);
        
            if ( empty($result_get_checker) )
                $this->checkerStatusApi($process, 'SecurityDescription', 'set', 0);
            else $this->checkerStatusApi($process, 'SecurityDescription', 'set', 1);

            return $data;

        } else return 404;
    }



    /**
     * Формируем ссылку для цены закрытия дивидендов к АПИ московской бирже и записываем ее в базу
     * 
     */
    private function addClosePriceDividendsLinkApiToDB(string $process, string $params_api)
    {
        $checker = $this->checkerStatusApi( $process, 'ClosePriceDividends', 'get' );
        if ( isset($checker) && $checker['status'] == 1 ) return false;
        
        // Дивиденды
        $db = [
            'columns' => 'secid, registryclosedate',
            'data'    => [ 'close_price' => 0 ]
        ];

        $get_dividends = $this->model_exchange_api->getDividends($db['columns'], $db['data']);
        if ( empty($get_dividends) ) return false;

        // Шаблон урл: https://iss.moex.com/iss/history/engines/stock/markets/shares/boards/TQBR/securities/AFKS/dividends?from=2014-07-17&till=2014-07-17
        foreach ($get_dividends as $val)
        {
            $date = $this->getDateClosePrice( $val['registryclosedate'] );
            $url_api[] = 'https://iss.moex.com/iss/history/engines/stock/markets/shares/boards/TQBR/securities/'. $val['secid'] .'.json?'. $params_api .'&from='. $date .'&till='. $date.'';
        }

        $data = [
            'process' => $process,
            'type'    => 'ClosePriceDividends',
            'source'  => 'moex',
            'api'     => $url_api //603 строки
        ];
        
        // Записываем в базу данных
        return $this->model_exchange_api->insertQueryForAPI($data);
    }



    /**
     * Получаем анные для обновления цен закрытия дивидендов к АПИ московской бирже
     * 
     */
    private function getClosePriceDividendsDataOnApi(string $process, int $limit)
    {
        $result_get = $this->model_exchange_api->getQueryForAPI( ['type' => 'ClosePriceDividends', 'process' => $process], $limit );
        if ( empty($result_get) ) return 404;

        $curl = $this->curl->multiThreads( $result_get );
        if ( empty($curl['result']) ) return 404;

        foreach ($curl['result'] as $val)
        {
            $json = json_decode($val, true);
            
            foreach ($json[1]['history'] as $history)
                $data[] = $history;
                //$result = $this->model_exchange_api->updateDividendsClosePrice ( array_change_key_case( $data, CASE_LOWER ));
            
        }

        if( !empty($data) )
        {
            $this->model_exchange_api->deleteQueryForAPIOnCurlData($curl['info']);
   
            $result_get_checker = $this->model_exchange_api->getQueryForAPI(['type' => 'ClosePriceDividends', 'process' => $process], 1);
            
            if ( empty($result_get_checker) )
                $this->checkerStatusApi($process, 'ClosePriceDividends', 'set', 0);
            else $this->checkerStatusApi($process, 'ClosePriceDividends', 'set', 1);

            return $data;

        } else return 404;
    }



    /**
     * Получаем строку опций для АПИ
     * $option_name - имя опций из массива $options_api_moex
     * 
     */
    private function getStringOptions(string ...$option_name)
    {
        if (!is_array($option_name)) return false;

        $arr = [];

        foreach($option_name as $options)
            $arr += $this->options_api_moex[$options];
        
        return http_build_query($arr);
    }



    /**
     * Проверяем статус процесса или устанавливаем его
     * 
     */
    private function checkerStatusApi( string $process, string $type, string $param = 'get', int $status = 1)
    {
        if ($param == 'get')
            return $this->model_exchange_api->selectStatusApi($process, $type);

        if ($param == 'set')
            return $this->model_exchange_api->insertStatusApi($process, $type, $status);
    }



    /**
     * 
     * 
     */
    private function getDateClosePrice( string $date) 
    {
        // Получаем порядковый номер дня недели ( от 0 - воскресенье до 6 - суббота )
        $date_time = new \DateTime($date);

        switch ( $date_time->format( 'w' ) ) 
        {
            case 0:
                $date_time->add( new \DateInterval('P1D') );
                $date = $date_time->format( 'Y-m-d' );
            break;

            case 6:
                $date_time->add( new \DateInterval('P2D') );
                $date = $date_time->format( 'Y-m-d' );
            break;
        }

        return $date;
    }
    



    



    /**
     * 
     * 
     */
    public function dataByCurl () 
    {
        $result  = [];
        $error = [];

        // Подключаем модели
        $this->load->model('', 'ExchangeApi');
        
        // Получаем данные по ценным бумагам
        $result_get = $this->model_exchange_api->getQueryForAPI(['type' => 'board'], 5);

        if ( !empty ($result_get) ) {
            $curl = $this->curl->multiThreads ($result_get);
            $result_json = json_decode ($curl['result'][0], true)[1];
    
            foreach ($result_json['securities'] as $securities)
                $result['securities'][$securities['SECID']] = $securities;
    
            foreach ($result_json['marketdata'] as $securities)
            $result['marketdata'][$securities['SECID']] = $securities;
    
            $this->model_exchange_api->deleteQueryForAPIOnCurlData($curl['info']);
        }
        
        
        // Описание ценной бумаги
        $result_get = $this->model_exchange_api->getQueryForAPI(['type' => 'description'], 50);
        
        if ( !empty ($result_get) ) {
            $curl = $this->curl->multiThreads ($result_get);

            foreach ($curl['result'] as $key => $val) {
                $result['description'][$key] = json_decode ($val, true)[1]['description'];
            }
    
            $this->model_exchange_api->deleteQueryForAPIOnCurlData($curl['info']);
        }
        
        // Дивиденды по ценным бумагам
        $result_get = $this->model_exchange_api->getQueryForAPI(['type' => 'dividends', 'source' => 'moex'], 50);

        if ( !empty ($result_get) ) {

            $curl = $this->curl->multiThreads ($result_get);
            
            foreach ($curl['result'] as $key => $val) 
            {
                $json = json_decode ($val, true)[1];
                if ( empty ($json['dividends']) ) continue;
                
                foreach ($json['dividends'] as $key_json => $dividents) 
                {
                    $date = new \DateTime($dividents['registryclosedate']);
                    $json['dividends'][$key_json]['month_close_date'] = $date->format('Y-m-00');
                    $json['dividends'][$key_json]['date_close_price'] = $this->getDateClosePrice( $dividents['registryclosedate'] );
                }
    
                $result['dividends'][$key] = $json['dividends'];
            }
    
            $this->model_exchange_api->deleteQueryForAPIOnCurlData($curl['info']);
        }
        
        $this->model_exchange_api->insertData ($result);

        
        /** --- Парсим Yooho --- **/

        $result_get['yooho_dividends_query'] = [];
        $result_get['yooho_dividends_query'] += $this->model_exchange_api->getQueryForAPI(['type' => 'dividends', 'source' => 'yooho_il'], 50);
        $result_get['yooho_dividends_query'] += $this->model_exchange_api->getQueryForAPI(['type' => 'dividends', 'source' => 'yooho_me'], 50);
        
        if ( !empty ($result_get['yooho_dividends_query']) ) 
        {
            $curl = $this->curl->multiThreads ($result_get['yooho_dividends_query']);

            $date = new \DateTime();
            $date->setTimezone(new \DateTimeZone('Europe/Moscow'));

            $result['dividends'] = [];

            foreach ($curl['result'] as $json) {

                $json_data = json_decode ($json, true);

                if (empty($json_data['chart']['result'])) continue;

                $chart = $json_data['chart']['result'][0];
                
                if (!isset ($chart['events']) || !isset($chart['events']['dividends'])) continue;

                foreach ($chart['events']['dividends'] as $div_yooho) {

                    $symbol = preg_replace('~\.[A-Z]+$~', '$1', $chart['meta']['symbol']);

                    $result['dividends'][$symbol][] = [
                        'secid'             => $symbol,
                        'registryclosedate' => $date->setTimestamp($div_yooho['date'])/*->modify('+1 day')*/->format('Y-m-d'),
                        'value'             => $div_yooho['amount'],
                        'currencyid'        => $chart['meta']['currency'],
                        'month_close_date'  => $date->setTimestamp($div_yooho['date'])->format('Y-m-00'),
                        'date_close_price'  => $this->getDateClosePrice( $date->setTimestamp( $div_yooho['date'] )->format('Y-m-d') ),
                    ];
                }
            }

            $result_add = $this->model_exchange_api->addDividends ($result['dividends']);
            //d($curl['info']);
            //if ($result_add == 200)
                $this->model_exchange_api->deleteQueryForAPIOnCurlData($curl['info']);
        }

        
        /** ---  Добавляем цену закрытия дивиденда у которых НОЛЬ по moex --- */
        $result_get = $this->model_exchange_api->getQueryForAPI(['type' => 'close_price_dividends'], 50);
        
        if ( !empty ($result_get)) {

            $curl = $this->curl->multiThreads ($result_get);
            
            $result['dividends'] = [];

            foreach ($curl['result'] as $json) {
                $json_data = json_decode ($json, true);
                
                foreach ($json_data[1]['history'] as $data) {
                    
                    if (!empty ($data))
                        $this->model_exchange_api->updateDividendsClosePrice ( array_change_key_case( $data, CASE_LOWER ));
                    
                }
                
            }

            $this->model_exchange_api->deleteQueryForAPIOnCurlData($curl['info']);
        }
    } // End: getDataCurl



    

}