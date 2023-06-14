<?php
namespace library;

use 
    controller\ExchangeApi,
    model\ExchangeApi AS ModelExchangeApi,
    library\Curl;


class Cron 
{
    protected $registry;
    private $model_exchange_api;
    private $curl;
	
	public function __construct($registry)
	{
	   	$this->registry = $registry;
        $this->model_exchange_api = new ModelExchangeApi($registry);
        $this->curl = new Curl();
		
	} // End: function __construct

    
    public function dataByCurl () 
    {
        $result  = [];
        $error = [];

        // Подключаем модели
        //$this->load->model('', 'Exchange');
        
        // Получаем данные по ценным бумагам
        $result_get = $this->model_exchange_api->getQueryForAPI( ['type' => 'board'], 5 );

        if ( !empty ($result_get) ) {
            $curl = $this->curl->multiThreads ($result_get);
            $result_json = json_decode ($curl['result']['board/moex/TQBR'], true)[1];
    
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

        if ( !empty ($result_get) ) 
        {

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
        
        if ( !empty ($result_get['yooho_dividends_query']) ) {
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

        
        /** ---  Добавляем цену закрытия дивиденда --- */
        $result_get = $this->model_exchange_api->getQueryForAPI(['type' => 'close_price_dividends'], 50);

        if ( !empty ($result_get)) {

            $curl = $this->curl->multiThreads ($result_get);
        
            $result['dividends'] = [];

            foreach ($curl['result'] as $json) {
                $json_data = json_decode ($json, true);
                
                foreach ($json_data[1]['history'] as $data) {
                    
                    if (!empty ($data))
                        $this->model_exchange_api->updateDividendsClosePrice ( array_change_key_case ($data, CASE_LOWER));
                    
                }
                
            }

            $this->model_exchange_api->deleteQueryForAPIOnCurlData($curl['info']);
        }
    } // End: getDataCurl



    /**
     * 
     * 
     */
    private function getDateClosePrice( string $date = '' ) 
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


}
