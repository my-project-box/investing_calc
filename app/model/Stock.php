<?php
namespace model;

use 
    library\Database,
    engine\Model;

class Stock extends Model
{
    
    /**
     * 
     * 
     */
    public function all () 
    {   
        $sql = 'SELECT * FROM stock';
        return $this->db->query($sql, $params = null);
    }



    /**
     * 
     * 
     */
    public function insertDataByCurl (array $data = []) 
    {
        $values_stock        = '';
        $on_duplcate_key     = '';
        $params_stock              = [];

        $values_stock_boards = '';
        $params_stock_boards = [];

        foreach ($data as $i => $val) {
            
            $values_stock .= '(:secid_'. $i .', :name_'. $i .', :full_name_'. $i .', :isin_'. $i .', :issued_size_'. $i .', :lot_size_'. $i .', :last_price_'. $i .'),';
            
            $params_stock['secid_' . $i]       = $val['secid'];
            $params_stock['name_' . $i]        = $val['shortname'];
            $params_stock['full_name_' . $i]   = $val['secname'];
            $params_stock['isin_' . $i]        = $val['isin'];
            $params_stock['issued_size_' . $i] = $val['issuesize'];
            $params_stock['lot_size_' . $i]    = $val['lotsize'];
            $params_stock['last_price_' . $i]  = $val['last_price'];

            
            $values_stock_boards .= '(:secid_'. $i .', :boardid_'. $i .'),';

            $params_stock_boards['secid_' . $i]   = $val['secid'];
            $params_stock_boards['boardid_' . $i] = $val['boardid'];

            //break;
        }
        
        $sql = '
            INSERT INTO stock (secid, name, full_name, isin, issued_size, lot_size, last_price) 
            VALUES '. rtrim($values_stock, ',') .'
            ON DUPLICATE KEY UPDATE last_price = VALUES(last_price)
        ';

        $result = $this->db->execute($sql, $params_stock);

        $sql = 'INSERT INTO stock_boards (secid, boardid) VALUES '. rtrim($values_stock_boards, ',') .'';
        $this->db->execute($sql, $params_stock_boards);

        return $result;
    }



    /**
     * 
     * 
     */
    public function insertDataByCurl2 (array $data = []) 
    {
        
        $result = [];

        /* --- Грузим в базу акции --- */

        $columns_securities = '`'. str_replace (',', '`,`', strtolower (implode (',', $data['securities']['columns']))) .'`';
        $marker_securities  = '';
        $params_securities  = [];
        $odku_securities    = '';

        foreach ($data['securities']['data'] as $security) {
            $marker_securities .= '('. implode (',', array_fill (0, count ($security), '?')) .'),';
            $params_securities = array_merge ($params_securities, $security);
        }

        foreach ($data['securities']['columns'] as $column_securities) {
            $odku_securities .= '`'. strtolower ($column_securities) .'` = VALUES(`'. strtolower ($column_securities) .'`),';
        }


        $sql = '
            INSERT INTO moex_securities ('. $columns_securities .') 
            VALUES '. rtrim($marker_securities, ',') .'
            ON DUPLICATE KEY UPDATE '. rtrim($odku_securities, ',') .'
        ';

        $result_securities = $this->db->execute($sql, $params_securities);

        if ($result_securities['result'] == 1)
            $result['securities'] = 200;

        
        /* --- Грузим в базу данные по рынку --- */

        $columns_securities_marketdata = '`'. str_replace (',', '`,`', strtolower (implode (',', $data['marketdata']['columns']))) .'`';
        $marker_securities_marketdata  = '';
        $params_securities_marketdata  = [];
        $odku_securities_marketdata    = '';

        foreach ($data['marketdata']['data'] as $marketdata) {
            $marker_securities_marketdata .= '('. implode (',', array_fill (0, count ($marketdata), '?')) .'),';
            $params_securities_marketdata = array_merge ($params_securities_marketdata, $marketdata);
        }

        foreach ($data['marketdata']['columns'] as $column_marketdata) {
            $odku_securities_marketdata .= '`'. strtolower ($column_marketdata) .'` = VALUES(`'. strtolower ($column_marketdata) .'`),';
        }


        $sql = '
            INSERT INTO moex_securities_marketdata ('. $columns_securities_marketdata .') 
            VALUES '. rtrim($marker_securities_marketdata, ',') .'
            ON DUPLICATE KEY UPDATE '. rtrim($odku_securities_marketdata, ',') .'
        ';

        $result_marketdata = $this->db->execute($sql, $params_securities_marketdata);

        if ($result_marketdata['result'] == 1)
            $result['securities_marketdata'] = 200;

        //d($data['securities']['data']);
        //d($data['securities']['columns']);
        //d($columns_securities);
        d($result);

        //return $result;
    }



}