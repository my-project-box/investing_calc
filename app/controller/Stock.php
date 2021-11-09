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
        $a = $this->model_stock->all();
        d($a);
        require_once 'app/view/stock.php';
        return true;
    }


    public function getDataCurl () 
    {
        $this->load->model('', 'Stock');
        
        $url = [
            'tqbr' => 'https://iss.moex.com/iss/engines/stock/markets/shares/boards/TQBR/securities.json'
        ];

        $curl = $this->curl->query($url['tqbr']);
        $result = json_decode($curl['result'], true);

        $securities = [];

        foreach ($result['securities']['data'] as $security_value) {
            $security = array_change_key_case(array_combine($result['securities']['columns'] , $security_value));

            foreach ($result['marketdata']['data'] as $marketdata_value) {

                $marketdata = array_change_key_case(array_combine($result['marketdata']['columns'] , $marketdata_value));

                if ($marketdata['secid'] == $security['secid'])
                    $security['last_price'] = $marketdata['last'];
                
            }

            /*$curl2 = $this->curl->query('https://iss.moex.com/iss/securities/' . $security['secid'] . '.json');
            $result2 = json_decode($curl2['result'], true);

            if (in_array('TYPENAME', $result2['description']['data'][12]))
                $security['type'] = $result2['description']['data'][12][2];*/

            $securities[] = $security;
        }
        
        $this->model_stock->insertDataByCurl($securities);
        //d($securities);
    }


}