<?php
namespace model;

use 
    library\Database,
    engine\Model;

class User extends Model
{

    /**
     * 
     * 
     */
    public function getDataAll ($login) 
    {
        
        $sql = 'SELECT * FROM user WHERE login = :login';
        $params = ['login' => $login];
        $result = $this->db->query($sql, $params);

        return array_shift ($result);
    }



    /**
     * 
     * 
     */
    public function insert ( array $data = [] ) 
    {
        $sql = 'INSERT INTO user SET login = :login, password = :password';
        $params = ['login' => $data['login'], 'password' => password_hash($data['password'], PASSWORD_BCRYPT)];

        if (isset ($data['role']))
            $params['role'] = $data['role'];

        return $this->db->execute($sql, $params);
    }



    /**
     * Вставляем запись покупки по активу
     * 
     */
    public function inserUserAssetBuy ( array $data = [] ) 
    {
        $params = [ 
            'secid'    => $data['secid'], 
            'user_id'  => $data['user_id'],
            'quantity' => $data['quantity'],
            'price'    => str_replace( ',', '.', $data['price'] ),
            'date'     => $data['date']
        ];

        $sql = '
            INSERT INTO user_asset 
            SET 
                secid    = :secid,
                user_id  = :user_id,
                quantity = :quantity,
                price    = :price,
                date     = :date
            ON DUPLICATE KEY UPDATE
                quantity = :quantity,
                price    = :price
            ';

        return $this->db->query ( $sql, $params );
    }



    /**
     * Получаем все покупки актива
     * 
     */
    public function getUserAssetBuy ( array $data = [] ) 
    {
        $params = [ 'secid' => $data['secid'], 'user_id' => $data['user_id'] ];
        $sql = 'SELECT `quantity`, `price`, `date` FROM user_asset WHERE secid = :secid AND user_id = :user_id';

        return $this->db->query ( $sql, $params );
    }



    /**
     * Удаляем запись покупки по активу
     * 
     */
    public function deleteUserAssetBuy ( array $data = [] ) 
    {
        $params = [ 
            'secid'    => $data['secid'], 
            'user_id'  => $data['user_id'],
            'date'     => $data['date']
        ];

        $sql = 'DELETE FROM user_asset WHERE secid = :secid AND user_id = :user_id AND date = :date';

        return $this->db->execute ( $sql, $params );
    }


}