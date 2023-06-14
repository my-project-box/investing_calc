<?php
namespace model;

use 
    library\Database,
    engine\Model;

class Stock extends Model
{
    
    /**
     * Получаем все активы
     * 
     */
    public function getSecurities ($data = []) 
    {   
        $sql = '
            SELECT s.*, msb.secname, msb.lotsize, msb.issuesize, msb.faceunit, msb.listlevel, msm.last
        ';

        if ( isset ($data['user_id']) && !empty ($data['user_id']) )
            $sql .= ', IF (us.actual_price > 0, ROUND(((msm.last / us.actual_price - 1) * 100), 2), 0) as percent_of_price_market, us.assets, us.actual_price, us.portfolio_id';

        $sql .= '
            FROM moex_securities AS s
            LEFT JOIN moex_securities_marketdata AS msm ON msm.secid = s.secid
            LEFT JOIN moex_securities_boards AS msb ON msb.secid = s.secid
        ';

        if ( isset ($data['user_id']) && !empty ($data['user_id']) ) 
        {
            $sql .= 'LEFT JOIN moex_securities_custom_info AS us ON us.secid = s.secid AND us.user_id = :user_id';
            $params['user_id'] = $data['user_id'];
        }

        $sql .= '
            WHERE msm.boardid = :boardid
        ';
 
        $params['boardid'] = 'tqbr';

        if ( isset ($data['portfolio_id']) && !empty ($data['portfolio_id']) ) 
        {
            $sql .= ' AND (us.portfolio_id = :portfolio_id';

            if ($data['portfolio_id'] == 'all') 
                $sql .= ' OR us.portfolio_id IS NULL';

            $sql .= ')';

            $params['portfolio_id'] = $data['portfolio_id'];
        }

        if ( isset ($data['listlevel']) && !empty ($data['listlevel']) ) 
        {
            $sql .= ' AND msb.listlevel = :listlevel';
            $params['listlevel'] = $data['listlevel'];
        }

        if ( isset ($data['idea']) && !empty ($data['idea']) ) 
        {
            $sql .= ' AND s.idea = :idea';
            $params['idea'] = $data['idea'];
        }

        $sql .= ' ORDER BY s.dividends_yield_years DESC'; //, percent_of_price_market DESC
        //d($sql);
        return $this->db->query($sql, $params);
    }



    /**
     *  Получаем данные по активу
     * 
     */
    public function getSecurity (array $data = []) 
    {
        !is_array ( $data ) ? [ $data ] : $data;
        
        $sql = '
            SELECT s.*, msb.issuesize, msb.secname, msm.last as price, us.assets, us.portfolio_id
            FROM moex_securities AS s
            LEFT JOIN moex_securities_boards AS msb ON msb.secid = s.secid
            LEFT JOIN moex_securities_marketdata AS msm ON msm.secid = s.secid
            LEFT JOIN moex_securities_custom_info AS us ON us.secid = s.secid AND us.user_id = :user_id
            WHERE s.secid = :secid 
        ';

        $params = [ 'secid' => $data['secid'], 'user_id' => $data['user_id'] ];
        $response = $this->db->query ( $sql, $params );

        return array_shift ( $response );
    }



    /**
     *  Обновляем данные по активу
     * 
     */
    public function updateSecurity (array $data = []) 
    {
        $sql = '
            INSERT INTO moex_securities_custom_info
            SET 
                secid        = :secid, 
                user_id      = :user_id,
                assets       = :assets,
                actual_price = :actual_price,
                portfolio_id = :portfolio_id
            ON DUPLICATE KEY UPDATE
                assets       = :assets,
                actual_price = :actual_price,
                portfolio_id = :portfolio_id
        ';

        $params = [
            'secid'        => $data['secid'], 
            'user_id'      => $data['user_id'],
            'assets'       => $data['assets'], 
            'actual_price' => $data['actual_price'],
            'portfolio_id' => $data['portfolio_id']
        ];

        return $this->db->execute($sql, $params);
    }



    /**
     * Добавляем актив в идею
     * 
     */
    public function addSecurityToIdea ($data = []) 
    {
        $sql = 'INSERT INTO moex_securities SET secid = :secid, idea = :idea ON DUPLICATE KEY UPDATE idea = :idea';
        $params = [ 'secid' => $data['secid'], 'idea' => $data['idea'] ];

        /*if ( isset ($data['idea']) && $data['idea'] == 'on' )
            $params['idea'] = 1;*/

        return $this->db->execute($sql, $params);
    }



    /**
     *  Удаляем данные по активу
     * 
     */
    public function deleteSecurity (array $data = []) 
    {
        $sql = '
            DELETE FROM moex_securities_custom_info WHERE secid = :secid AND user_id = :user_id
        ';

        $params = [
            'secid'        => $data['secid'], 
            'user_id'      => $data['user_id']
        ];

        return $this->db->execute($sql, $params);
    }



    /**
     * Получаем тикет имитента
     * 
     */
    public function secidAll () 
    {   
        $sql = 'SELECT DISTINCT secid FROM moex_securities_boards';

        $result = [];

        foreach ($this->db->query($sql, $params = null) as $val)
            $result[] = $val['secid'];

        return $result;
    }



    /**
     * Получаем дивиденды имитента
     *  = ['secid' => false, 'current_year' => false, 'date_from' => false, 'date_to' => false]
     */
    public function getDividends ($data = [], $current_year = false) 
    {
        $result = [];
        $sqlArr = [];
        $params = [];

        $date = false;

        if ((isset($data['date_from']) && isset($data['date_to'])) && (!empty($data['date_from']) && !empty($data['date_to']))) {
            $date = ' AND registryclosedate BETWEEN :date_from AND :date_to';
            $params = array_merge ( $params, ['date_from' => $data['date_from'], 'date_to' => $data['date_to']] );
        }

        if ( isset ($data['currency']) )
            $currency = 'AND currencyid = (SELECT REPLACE(msb.faceunit, "SUR", "RUB") AS rtr FROM moex_securities_boards AS msb WHERE msb.secid = msd.secid)';
        else $currency = false;

        $sql = '
            SELECT msd.secid,
                ( SELECT COUNT(DISTINCT YEAR(registryclosedate)) FROM moex_securities_dividends WHERE secid = msd.secid ) AS count_years, 
                ( SELECT MAX(DISTINCT YEAR(registryclosedate)) FROM moex_securities_dividends WHERE secid = msd.secid ) AS max_year,
                ( SELECT MIN(DISTINCT YEAR(registryclosedate)) FROM moex_securities_dividends WHERE secid = msd.secid ) AS min_year,
                ( SELECT SUM(value) FROM moex_securities_dividends WHERE secid = msd.secid '. $currency . $date .') AS sum_dividends,
                ( SELECT SUM(close_price) FROM moex_securities_dividends WHERE secid = msd.secid '. $currency . $date .') AS sum_price,
            msd.currencyid FROM moex_securities_dividends AS msd 
        ';

        if (isset ($data['secid']) && !empty ($data['secid']))
            $sql .= ' WHERE ';

        if (isset($data['secid'])) {
            $sqlArr[] = 'msd.secid = :secid';
            $params['secid'] = $data['secid'];
        }

        $sql .= implode (' AND ', $sqlArr);

        $sql .= ' GROUP BY msd.secid';
       
        $dividends = $this->db->query($sql, $params);

        if ( isset ($data['all']) )
            return $dividends;

        $allYearsDididends = $this->getAllYearsDididends ();
        
        foreach ($dividends as $dividend) 
        {
            $secid = $dividend['secid'];
            $result[$secid] = $dividend;
            $result[$secid]['yield_years'] = $dividend['sum_dividends'] != 0 && $dividend['sum_price'] != 0 ? round( ($dividend['sum_dividends'] / $dividend['sum_price']) * 100, 2 ) : 0;
            
            /** Получаем регулярность выплат дивидендов в годах */

            $allYearsFromMinToMax = [];

            for ($dividend['min_year']; $dividend['min_year'] <= $dividend['max_year']; $dividend['min_year']++) 
                $allYearsFromMinToMax[] = $dividend['min_year'];

            $yearsChecked = [];

            foreach (array_reverse ($allYearsFromMinToMax) as $val) {
                if (in_array ($val, $allYearsDididends[$secid])) $yearsChecked[] = $val;
                else break;
            }
            
            $result[$secid]['years_checked'] = count($yearsChecked);
        }
        
        foreach ($result as $imitent)
            $addAveragePercent[] = ['secid' => $imitent['secid'], 'dividends_yield_years' => $imitent['yield_years']];
        
        if ( isset ($addAveragePercent) )
            $this->addAveragePercentDividend ($addAveragePercent);

        return $result;
    }



    /**
     * Получаем максимальную и минимальную дату закрытия по имитенту
     * 
     */
    public function getDividendsDateMaxMin () 
    {
        $sql = '
            SELECT DISTINCT secid, 
                (SELECT MIN(registryclosedate) FROM moex_securities_dividends WHERE secid = msd.secid) AS date_from,
                (SELECT MAX(registryclosedate) FROM moex_securities_dividends WHERE secid = msd.secid) AS date_to
            FROM moex_securities_dividends AS msd
        ';

        return $this->db->query($sql, []);
    }



    /**
     * Получаем дату регистрации по дивиденду
     * 
     */
    public function getRegistryCloseDateDividends() 
    {
        $sql = '
            SELECT msd.secid, msd.registryclosedate 
            FROM moex_securities_dividends AS msd 
            WHERE currencyid = (SELECT REPLACE(msb.faceunit, "SUR", "RUB") AS rtr FROM moex_securities_boards AS msb WHERE msb.secid = msd.secid
        )';

        foreach ( $this->db->query($sql, []) as $value )
            $result[$value['secid']][] = $value['registryclosedate'];

        return $result;

    }



    /**
     * Добавляем имитенту средний процент доходности по дивидендам
     * 
     */
    public function addAveragePercentDividend (array $data = []) 
    {
        $marker  = '';
        $columns_string = '';
        $params  = [];
        
        if ( !empty ($data) ) {
            $columns = array_keys ($data[0]);
            $columns_string = '`'. str_replace (',', '`,`', strtolower ( implode (',', $columns ))) .'`';
        
        
            foreach ($data as $arr_val) {
                $marker .= '('. implode (',', array_fill (0, count($arr_val), '?') ) .'),';

                foreach ($arr_val as $val)
                    $params[] = $val;
            }

            $sql = '
                INSERT INTO moex_securities ('. $columns_string .') 
                VALUES '. rtrim($marker, ',') .'
                ON DUPLICATE KEY UPDATE dividends_yield_years = VALUES(dividends_yield_years)
            ';

        } else {
            $sql = 'UPDATE moex_securities SET dividends_yield_years = 0';
        }
        
        $this->db->execute($sql, $params);
    }



    /**
     * Получаем все года выплат по дивидендам имитента
     * 
     */
    public function getAllYearsDididends () 
    {
        $sql = 'SELECT secid, YEAR(month_close_date) AS year FROM moex_securities_dividends GROUP BY secid, YEAR(month_close_date)';
        $params = [];
        $query = $this->db->query ($sql, $params);

        $resulrt = [];

        foreach ($query as $imitent) 
            $result[$imitent['secid']][] = $imitent['year'];

        if ( !empty($result) )
            return $result;
    }

}