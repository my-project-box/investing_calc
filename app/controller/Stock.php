<?php
namespace controller;

use 
    engine\Controller,
    model\Modelstock;

class Stock extends Controller
{
    
    public function index ()
    {
        $this->load->model('', 'Stock');
        $securities = $this->model_stock->all ();
        
        foreach ($securities as $key => $security) {

            $data = json_decode($security['data'], true);
            $data_key = [];
            foreach ($data as $val) 
                $data_key[] = $val[0];
            
            $new_data = array_combine($data_key, $data);
            $securities[$key]['type'] = $new_data['TYPENAME'][2];
        }
        //extract();
        //d($securities);
        require_once 'app/view/page/stock.php';
        return true;
    }


    public function getDataCurl () 
    {
        $result = [];
        $this->load->model('', 'Stock');
        
        $urls_boards = [
            'tqbr' => 'https://iss.moex.com/iss/engines/stock/markets/shares/boards/TQBR/securities.json',
        ];

        $curl_securities = $this->curl->multi ($urls_boards);

        foreach ($curl_securities['result'] as $val)
            $result = json_decode ($val, true);

        
        /* --- Получаем данны по конкретному имитенту в $result['security'] --- */

        $securities_secid = $this->model_stock->secid ();
        $urls_security = [];

        foreach ($securities_secid as $secid) {
            $urls_security['description'][$secid] = 'https://iss.moex.com/iss/securities/' . $secid . '.json';
            $urls_security['dividends'][$secid]  = 'https://iss.moex.com/iss/securities/' . $secid . '/dividends.json';
        }

        $curl_security_description = $this->curl->multiThreads ($urls_security['description']);

        foreach ($curl_security_description['result'] as $key => $val)
            $result['security'][$key] = json_decode ($val, true);


        $curl_security_dividends = $this->curl->multiThreads ($urls_security['dividends']);

        foreach ($curl_security_dividends['result'] as $key => $val)
            $result['security'][$key] += json_decode ($val, true);
            
        $this->model_stock->insertDataByCurl ($result);
    }


}