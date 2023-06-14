<?php
namespace model;

use 
    library\Database,
    engine\Model;

class ExchangeApi extends Model
{
    private array  $code = [
        'ok' => 200,
        'error' => 404
    ];
    


    /**
     *  Вставляем ссылки для API в базу
     * 
     */
    public function insertQueryForAPI (array $data = []) 
    {
        if ( !is_array($data) ) return false;

        $marker  = '';
        $params  = [];
        $columns = ['id', 'type', 'process', 'source', 'query'];
        $num = 0;

        $columns_string = '`'. implode ('`,`', $columns) .'`';
        
        foreach ($data['api'] as $key => $urls) {
                    
            if ( is_array($urls) )
            {
                foreach ($urls as $url)
                {
                    $id = $data['type'] . $data['process'] . microtime(true) . $num;

                    $params[] = md5($id);
                    $params[] = $data['type'];
                    $params[] = $data['process'];
                    $params[] = $key;
                    $params[] = $url;

                    $marker  .= '('. implode (',', array_fill (0, count ($columns), '?') ) .'),';

                    $num++;
                }
            }
            else
            {
                $id = $data['type'] . $data['process'] . microtime(true) . $num;

                $params[] = md5($id);
                $params[] = $data['type'];
                $params[] = $data['process'];
                $params[] = $data['source'];
                $params[] = $urls;

                $marker  .= '('. implode (',', array_fill (0, count ($columns), '?') ) .'),';
            }

            if ( !is_array($urls) ) $num++;
        }
        
        $sql = '
            INSERT INTO api ('. $columns_string .') 
            VALUES '. rtrim($marker, ',') .'
            ON DUPLICATE KEY UPDATE `query` = VALUES(`query`)
        ';
       
        $result = $this->db->execute($sql, $params);
       
        if ($result['error'][0] > 0) return $this->code['error'];
        else return $this->code['ok'];
    }



    /**
     *  Проверяем наличие запросов к API
     * 
     */
    public function getQueryForAPI (array $data = [], int $limit = 0) 
    {
        
        if ( !is_array($data) || empty($data) ) return false;

        $key    = '';
        $query  = [];
        $string = '';

        $sql = 'SELECT `id`, `type`, `query` FROM api';

        if ( !empty ($data['type']) || !empty ($data['limit']) )
            $sql .= ' WHERE ';

        if ( !empty ($data['type']) ) {
            $string .= ' AND `type` = :type';
            $params['type'] = $data['type'];
        }

        if ( !empty ($data['source']) ) {
            $string .= ' AND `source` = :source';
            $params['source'] = $data['source'];
        }

        if ( !empty ($data['process']) ) {
            $string .= ' AND `process` = :process';
            $params['process'] = $data['process'];
        }

        $sql .= ltrim($string, 'AND ');

        if ( !empty ($limit) )
            $sql .= ' LIMIT '. $limit .''; 

        foreach ($this->db->query ($sql, $params) as $result) {
            $query[$result['id']] = $result['query'];
        }
        
        return $query;
    }



    /**
     * Удаляем запрос API
     * 
     * 
     */
    public function deleteQueryForAPIOnCurlData (array $data = []) 
    {
        if ( !is_array ($data) || empty ($data) ) return $code = 404;

        $sql = 'DELETE FROM api WHERE `id` = :id';
        $params = [];
        
        foreach ($data as $key => $query) {

            //if ($query['http_code'] == 200) {

                /*$columns_value = explode('/', $key);
                $columns = array_combine (['type', 'source', 'sec_id'], $columns_value);

                foreach ($columns as $column_key => $value)
                    $params[$column_key] = $value; */
                    $params['id'] = $key;

                $this->db->execute($sql, $params);
            //}

        } 

    }



    /**
     * Грузим в базу акции
     * 
     * 
     */
    public function addSecuritiesBoards ($data = []) 
    {
        $marker  = '';
        $params  = [];
        $odku    = '';

		if (is_array ($data) && empty ($data)) return false;
		
        foreach ($data as $securities) {
            $marker .= '('. implode (',', array_fill (0, count ($securities), '?')) .'),';

            foreach ($securities as $security)
                $params[] = $security;
            
        }
		
		$columns = array_flip (array_shift ($data));
        $columns_string = '`'. str_replace (',', '`,`', strtolower ( implode (',', $columns ))) .'`';
        
        foreach ($columns as $columns_securities)
            $odku .= '`'. strtolower ($columns_securities) .'` = VALUES(`'. strtolower ($columns_securities) .'`),';

        $sql = '
            INSERT INTO moex_securities_boards ('. $columns_string .') 
            VALUES '. rtrim($marker, ',') .'
            ON DUPLICATE KEY UPDATE '. rtrim($odku, ',') .'
        ';
        
        $result = $this->db->execute($sql, $params);
        
        if ($result['result'] == 1)
            return 200; 
    }



    /**
     * Грузим в базу данные по рынку
     * 
     * 
     */
    public function addSecuritiesMarketdata ($data = []) 
    {
        $marker  = '';
        $params  = [];
        $odku    = '';
        
		if (is_array ($data) && empty ($data)) return false;
		
        foreach ($data as $marketdata) {
            $marker .= '('. implode (',', array_fill (0, count ($marketdata), '?')) .'),';

            foreach ($marketdata as $marketdata_val)
                $params[] = $marketdata_val;
        }

        foreach (array_shift ($data) as $key => $val)
            $odku .= '`'. strtolower ($key) .'` = VALUES(`'. strtolower ($key) .'`),';

        $sql = '
            INSERT INTO moex_securities_marketdata (`secid`, `boardid`, `last`) 
            VALUES '. rtrim($marker, ',') .'
            ON DUPLICATE KEY UPDATE '. rtrim($odku, ',') .'
        ';
        
        $result = $this->db->execute($sql, $params);
        //dd($result);
        if ($result['result'] == 1)
            return 200;
    }



    /**
     * Грузим в базу данные по имитенту
     * 
     * 
     */
    public function addSecurities ($data = []) 
    {
        $marker  = '';
        $params  = [];

        foreach ($data as $key => $description) {

            $marker .= '('. implode (',', array_fill (0, 2, '?')) .'),';

            if (!empty($description))
                $data_security_description = json_encode ($description, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
            
                $arr = explode ('/', $key);
                $secid = end ($arr);
                
                $params[] = $secid;
                $params[] = $data_security_description;
        }

        $sql = '
            INSERT INTO moex_securities (`secid`, `data`) 
            VALUES '. rtrim($marker, ',') .'
            ON DUPLICATE KEY UPDATE `data` = VALUES(`data`)
        ';
        
        $result = $this->db->execute($sql, $params);
        
        if ($result['result'] == 1)
            return 200;
    }



    /**
     * Грузим в базу данные по дивидентам имитента
     * 
     * 
     */
    public function addDividends (array $data = [], array $settings = [] ) 
    {
        $marker = '';
        $columns = '';
        $params = [];
        
        foreach ($data as $tiker_columns) {
            if (empty($tiker_columns)) continue;

            $columns .= '`'. str_replace (',', '`,`', implode (',', array_keys ($tiker_columns[0]) ) ) .'`';
            break;
        }
        
        foreach ($data as $key => $dividends) {

            if (empty($dividends)) continue;   

            foreach ($dividends as $dividends_val) {
                $marker .= '('. implode (',', array_fill (0, count($dividends_val), '?')) .'),';

                foreach ($dividends_val as $dividends_param)
                    $params[] = $dividends_param;
            }
                    
        }
        
        $sql = '
            INSERT INTO moex_securities_dividends ('. $columns .') 
            VALUES '. rtrim($marker, ',') .'
            ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `date_time` = VALUES(`date_time`)
        ';

        if ( isset( $settings['date_close_price'] ) ) 
            $sql .= ', `date_close_price` = VALUES(`date_close_price`)';

        $result = $this->db->execute($sql, $params);
        
        if ($result['result'] == 1)
            return 200;
    }



    /**
     * Получаем из базы secid имитента, у которого close_price = 0
     * 
     */
    public function getDividends( string $columns, array $data, string $distinct = '' )
    {
        $sql = 'SELECT '. $distinct . ' '. $columns .' FROM moex_securities_dividends';

        if ( !empty($data) )
            $sql .= ' WHERE ';

        foreach ($data as $key => $val)
            $data_sql[] = $key. ' = :' .$key;

        $sql .= implode(' AND ', $data_sql);

        $params = [ 'close_price' => $data['close_price'] ];

        return $this->db->query( $sql, $params );
    }



    /**
     * Обнорвляем цену закрытия дивиденда у которых НОЛЬ по moex
     * 
     */
    public function updateDividendsClosePrice (array $data = []) 
    {
        $sql = '
            UPDATE moex_securities_dividends
            SET close_price = IF(close_price = 0, :close_price, close_price)
            WHERE secid = :secid AND date_close_price = :date_close_price
        ';

        $params = [ 'secid' => $data['secid'], 'date_close_price' => $data['tradedate'], 'close_price' => $data['legalcloseprice'] ];

        return $this->db->execute($sql, $params);
    }



    /**
     * 
     * 
     */
    public function selectStatusApi( string $process, string $type )
    {
        $sql = 'SELECT status FROM api_progress WHERE `process` = :process AND `type` = :type';
        $params = [ 'process' => $process, 'type' => $type ];
        $result = $this->db->query( $sql, $params );
        
        return array_shift( $result );
    }



    /**
     * 
     * 
     */
    public function insertStatusApi( string $process, string $type, int $status)
    {
        
        $sql = '
            INSERT INTO api_progress (`process`, `type`, `status`, `date_time`) 
            VALUES (?,?,?,?)
            ON DUPLICATE KEY UPDATE `status` = VALUES(`status`), `date_time` = VALUES(`date_time`)
        ';
        
        $params[] = $process;
        $params[] = $type;
        $params[] = $status;
        $params[] = date('Y-m-d H:i:s');
        
        $result = $this->db->execute($sql, $params);
        
        if ($result['error'][0] > 0) return $this->code['error'];
        else return $this->code['ok'];
    }



    /**
     * 
     * 
     */
    public function insertData (array $data = []) 
    {
        $result = [];
        
        $result['boards']     = isset ($data['securities']) ? $this->addSecuritiesBoards($data['securities']) : '';
        $result['marketdata'] = isset ($data['marketdata']) ? $this->addSecuritiesMarketdata($data['marketdata']) : '';
        $result['securities'] = isset ($data['description']) ? $this->addSecurities($data['description']) : '';
        $result['dividends']  = isset ($data['dividends']) ? $this->addDividends($data['dividends'], ['date_close_price' => true]) : '';
        d($result);
        return $result;
    }


}