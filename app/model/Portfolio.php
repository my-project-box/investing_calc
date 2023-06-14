<?php
namespace model;

use 
    library\Database,
    engine\Model;

class Portfolio extends Model
{

    /**
     * 
     * 
     */
    public function insert ( array $data = []) 
    {
        $params = [];
        $sql = 'INSERT INTO user_portfolio_description SET user_id = :user_id';
        

        if (isset ($data['user_id']))
            $params = ['user_id' => $data['user_id']];

        if (!empty ($data['name'])) {
            $sql .= ', name = :name';
            $params['name'] = $data['name'];
        }
            
        return $this->db->execute($sql, $params);
    }



    /**
     * 
     * 
     */
    public function delete ( array $data = []) 
    {
        $sql = 'DELETE FROM  user_portfolio_description WHERE id = :portfolio_id';
        $params = ['portfolio_id' => $data['portfolio_id']];

        return $this->db->execute($sql, $params);
    }



    /**
     * 
     * 
     */
    public function getPortfolios ( array $data = [] ) 
    {
        
        $sql = 'SELECT * FROM user_portfolio_description WHERE user_id = :user_id';
        $params = [ 'user_id' => $data['user_id'] ];
            
        return $this->db->query ( $sql, $params );
    }



    /**
     * Получаем все активы портфеля
     * 
     */
    public function getAssetsByPortfolioId( array $data = [] ) 
    {
        $sql = '
            SELECT msci.secid, msci.user_id, msci.portfolio_id, msb.secname, msm.last AS price,
            ( SELECT SUM(quantity) FROM user_asset WHERE user_id = msci.user_id AND secid = msci.secid ) AS quantity,
            ( 
                SELECT SUM( price*quantity ) FROM user_asset WHERE user_id = msci.user_id AND secid = msci.secid 

            ) AS total_buy_price,

            ( SELECT ROUND( ( ( ( SUM(quantity)*msm.last)/total_buy_price )-1 )*100 ) FROM user_asset WHERE user_id = msci.user_id AND secid = msci.secid ) AS current_yield
            FROM moex_securities_custom_info AS msci
            LEFT JOIN moex_securities_boards AS msb ON msb.secid = msci.secid
            LEFT JOIN moex_securities_marketdata AS msm ON msm.secid = msci.secid
            WHERE msci.user_id = :user_id AND msci.portfolio_id IN ('. implode (',', $data['portfolios_id']) .')
        ';
        $params = [ 'user_id' => $data['user_id'], ];

        foreach ( $data['portfolios_id'] as $portfolio_id ) 
        {
            foreach ( $this->db->query ( $sql, $params ) as $portfolio ) 
            {
                if ( $portfolio['portfolio_id'] ==  $portfolio_id) 
                    $result[$portfolio_id][] = $portfolio;
            }
            
        }
      
        return $result;
        
    }



    /**
     *  Удаляем актив
     * 
     */
    public function deleteAsset ( array $data = [] ) 
    {
        $params = [ 'user_id' => $data['user_id'], 'secid' => $data['tiker'], 'portfolio_id' => $data['portfolio_id'] ];

        $sql = 'SELECT assets FROM moex_securities_custom_info WHERE user_id = :user_id AND secid = :secid AND portfolio_id = :portfolio_id';
        $result = $this->db->query($sql, $params);
        $result = array_shift($result);

        if ( !empty ( $result['assets'] ) ) 
            $sql = 'UPDATE moex_securities_custom_info SET portfolio_id = 0 WHERE user_id = :user_id AND secid = :secid AND portfolio_id = :portfolio_id';
        else 
            $sql = 'DELETE FROM moex_securities_custom_info WHERE user_id = :user_id AND secid = :secid AND portfolio_id = :portfolio_id';
        
        return $this->db->execute($sql, $params);
    }



    /**
     *  Удаляем активы портфеля
     * 
     */
    public function deleteAssets ( array $data = [] ) 
    {
        $params = [ 'user_id' => $data['user_id'], 'portfolio_id' => $data['portfolio_id'] ];

        $sql = 'SELECT secid, assets FROM moex_securities_custom_info WHERE user_id = :user_id AND portfolio_id = :portfolio_id';
        $response = $this->db->query($sql, $params);

        foreach ( $response as $result ) 
        {
            if ( !empty ( $result['assets'] ) ) 
                $sql = 'UPDATE moex_securities_custom_info SET portfolio_id = 0 WHERE user_id = :user_id AND secid = :secid AND portfolio_id = :portfolio_id';
            else 
                $sql = 'DELETE FROM moex_securities_custom_info WHERE user_id = :user_id AND secid = :secid AND portfolio_id = :portfolio_id';

            $params['secid'] = $result['secid'];
            
            $this->db->execute($sql, $params);
        }
        
    }

}