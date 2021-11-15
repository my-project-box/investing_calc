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
    public function getSecurities () 
    {   
        $sql = '
            SELECT s.*, msb.secname, msb.lotsize, msb.issuesize, msb.faceunit, msm.last FROM moex_securities AS s
            LEFT JOIN moex_securities_marketdata AS msm ON msm.secid = s.secid
            LEFT JOIN moex_securities_boards AS msb ON msb.secid = s.secid
            WHERE msm.boardid = :boardid
        ';

        return $this->db->query($sql, ['boardid' => 'tqbr']);
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
     * 
     */
    public function getDividends (array $data) 
    {
        $result = [];
        
        $sql = 'SELECT * FROM moex_securities_dividends';
        $params = [];

        if ($data['secid']) {
            $sql .= ' WHERE secid = :secid';
            $params['secid'] = $secid;
        }
        
        foreach ($this->db->query($sql, $params) as $dividends) {

            $date_registry_close = new \Datetime ($dividends['registryclosedate']);
            $year_registry_close = $date_registry_close->format ('Y');
            if ($year_registry_close == date ('Y') && $data['current_year'] == false) continue;

           $result[$dividends['secid']][$year_registry_close][] = $dividends;
            krsort($result[$dividends['secid']]);
        }

        
        foreach ($result as $key => $dividends) {
            foreach ($dividends as $years_key => $years) {
                $sum   = 0;

                foreach ($years as $year)
                    $sum += $year['value'];

                //$result[$key][$years_key] = [];
                $result[$key][$years_key]['sum_value'] = $sum;
                //$result[$key][$years_key]['history'] = $years;
            }
            
        }


        foreach ($result as $key_imitent => $years) {
            $sum = 0;
            $count_years = count($years);

            foreach ($years as $year) 
                $sum += $year['sum_value'];
            
            $result[$key_imitent] = [];
            $result[$key_imitent]['average_value_total'] = round ($sum / $count_years, 2);
            $result[$key_imitent]['years']               = $years;
        }

        return $result;
    }



    /**
     * 
     * 
     */
    public function insertDataByCurl (array $data = []) 
    {
        
        $result  = [];
        $columns = '';
        $marker  = '';
        $params  = [];
        $odku    = '';

        /* --- Грузим в базу акции --- */

        $columns = '`'. str_replace (',', '`,`', strtolower (implode (',', $data['securities']['columns']))) .'`';

        foreach ($data['securities']['data'] as $security) {
            $marker .= '('. implode (',', array_fill (0, count ($security), '?')) .'),';
            $params = array_merge($params, $security);
        }

        foreach ($data['securities']['columns'] as $columns_securities)
            $odku .= '`'. strtolower ($columns_securities) .'` = VALUES(`'. strtolower ($columns_securities) .'`),';

        $sql = '
            INSERT INTO moex_securities_boards ('. $columns .') 
            VALUES '. rtrim($marker, ',') .'
            ON DUPLICATE KEY UPDATE '. rtrim($odku, ',') .'
        ';

        $result_securities = $this->db->execute($sql, $params);

        if ($result_securities['result'] == 1)
            $result['securities'] = 200;

        $marker  = '';
        $params  = [];
        $odku    = '';

        
        /* --- Грузим в базу данные по рынку --- */

        $columns = '`'. str_replace (',', '`,`', strtolower (implode (',', $data['marketdata']['columns']))) .'`';

        foreach ($data['marketdata']['data'] as $marketdata) {
            $marker .= '('. implode (',', array_fill (0, count ($marketdata), '?')) .'),';
            $params = array_merge($params, $marketdata);
        }

        foreach ($data['marketdata']['columns'] as $column_marketdata)
            $odku .= '`'. strtolower ($column_marketdata) .'` = VALUES(`'. strtolower ($column_marketdata) .'`),';

        $sql = '
            INSERT INTO moex_securities_marketdata ('. $columns .') 
            VALUES '. rtrim($marker, ',') .'
            ON DUPLICATE KEY UPDATE '. rtrim($odku, ',') .'
        ';

        $result_marketdata = $this->db->execute($sql, $params);

        if ($result_marketdata['result'] == 1)
            $result['securities_marketdata'] = 200;

        $marker  = '';
        $params  = [];
        $odku    = '';


        /* --- Грузим в базу данные по имитенту --- */

        foreach ($data['security'] as $key => $security) {

            $marker .= '('. implode (',', array_fill (0, 2, '?')) .'),';

            if (!empty($security['description']['data']))
                $data_security = json_encode ($security['description']['data'], JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
            
            $params = array_merge($params, [$key, $data_security]);
            
        }

        $sql = '
            INSERT INTO moex_securities (`secid`, `data`) 
            VALUES '. rtrim($marker, ',') .'
            ON DUPLICATE KEY UPDATE `data` = VALUES(`data`)
        ';

        $result_security = $this->db->execute($sql, $params);

        if ($result_security['result'] == 1)
            $result['security'] = 200;

        $marker  = '';
        $params  = [];
        $odku    = '';


        /* --- Грузим в базу данные по дивидентам имитента --- */

        $one_elem = array_shift($data['security']);
        $columns = '`'. str_replace (',', '`,`', strtolower (implode (',', $one_elem['dividends']['columns']))) .'`';

        foreach ($data['security'] as $key => $security) {

            if (empty($security['dividends']['data'])) continue;   

            foreach ($security['dividends']['data'] as $dividends) {
                $marker .= '('. implode (',', array_fill (0, count($security['dividends']['columns']), '?')) .'),';
                $params = array_merge($params, $dividends);
            }
                    
        }

        $sql = '
            INSERT INTO moex_securities_dividends ('. $columns .') 
            VALUES '. rtrim($marker, ',') .'
        ';

        $result_dividends = $this->db->execute($sql, $params);

        if ($result_dividends['result'] == 1)
            $result['dividends'] = 200;

        return $result;
    }



}