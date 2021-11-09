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



}