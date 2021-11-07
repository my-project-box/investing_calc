<?php
namespace model;

use 
    library\Database,
    engine\Model;

class Stock extends Model
{
    public function all () 
    {   
        $sql = 'SELECT * FROM stock';
        return $this->db->query($sql, $params = null);
    }
}