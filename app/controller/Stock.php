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


    public function getData () 
    {
        $url = [
            'tqbr' => 'https://iss.moex.com/iss/engines/stock/markets/shares/boards/TQBR/securities.json'
        ];

        $curl = $this->curl->query($url['tqbr']);
        $result = json_decode($curl['result'], true);

        $new_array = [];

        foreach ($result['securities']['data'] as $values) {
            $new_array[] = array_combine($result['securities']['columns'] , $values);
        }


        //d($result);
        d($new_array);
    }


}