<?php
namespace controller;

use 
    engine\Controller,
    model\Modelstock;

class Stock extends Controller
{
    
    /**
     * Главная страница
     * 
     */
    public function index ($current_year = false)
    {
        // Подключаем модели
        $this->load->model('', 'Stock');
        $this->load->model('', 'Portfolio');

        $data = [];
        $data_sort['user_id']   = isset($this->request->session()['user_id']) ? $this->request->session()['user_id'] : '';

        $data_sort['portfolio_id'] = isset($_GET['portfolio']) ? $_GET['portfolio'] : '';
        $data_sort['listlevel']    = isset($_GET['listlevel']) ? $_GET['listlevel'] : '';
        $data_sort['idea']         = isset($_GET['idea']) ? $_GET['idea'] : '';
        $data_sort['date_from']    = isset($_GET['date_from']) ? $_GET['date_from'] : '';
        $data_sort['date_to']      = isset($_GET['date_to'])   ? $_GET['date_to'] : '';

        $data['filter']  = $data_sort;

        $this->model_stock->addAveragePercentDividend ();

        $data_sort['currency'] = true;
        $dividends = $this->model_stock->getDividends ($data_sort);
        //d($dividends);
        $data['securities'] = $this->model_stock->getSecurities ($data_sort);
        
        foreach ($data['securities'] as $key => $security)
        {
            $json = json_decode ($security['data'], true);
            
            foreach ($json as $val)
            {
                if ($val['name'] =='TYPENAME') 
                {
                    $r = preg_replace ('~Акция ~iu', '', $val['value']);
                    $data['securities'][$key]['type'] = ucfirst ($r);
                }      
            }
            
            $secid = $security['secid'];

            $data['securities'][$key]['dividends_yield_years']      = isset ($dividends[$secid]['yield_years'])   ? $dividends[$secid]['yield_years'] : 0;
            $data['securities'][$key]['dividends_number_years_pay'] = isset ($dividends[$secid]['count_years'])   ? $dividends[$secid]['count_years'] : 0;
            $data['securities'][$key]['dividends_years_checked']    = isset ($dividends[$secid]['years_checked']) ? $dividends[$secid]['years_checked'] : 0;
            $data['securities'][$key]['dividends']                  = isset ($dividends[$secid])                  ? $dividends[$secid] : '';
        }

        $data['portfolios'] = $this->model_portfolio->getPortfolios ($data_sort);
        
        $data['title'] = 'Листинг акций российских комапний';

        $script = '<script src="../app/view/js/stock.js"></script>';
       // d($data);
        extract($data);
        
        require_once 'app/view/page/shares.php';
        return true;
    }



    /**
     * Страница конкретного актива
     * 
     */
    public function page (string $tiker = '', string $action = '') 
    {
        // Подключаем модели
        $this->load->model('', 'Stock');
        $this->load->model('', 'User');

        $data      = [];
        $data_sort = [];

        $url = str_replace ('/delete', '', array_key_first ( $this->request->get() ) );

        $data_sort = array_merge ($this->request->get(), [ 'secid' => $tiker], $this->request->session());

        if ( isset ($this->request->get()['add']) && $this->request->get()['add'] ) 
        {
            $this->model_user->inserUserAssetBuy ( $data_sort );
            header('Location: /'. $url );
        }

        if ( $action == 'delete')
        {
            $this->model_user->deleteUserAssetBuy ( $data_sort );
            header('Location: /'. $url);
        }

        $data = $this->model_stock->getSecurity ( [ 'secid' => $tiker, 'user_id' => $this->request->session()['user_id'] ] );
        $data['assets'] = $this->model_user->getUserAssetBuy ( [ 'secid' => $tiker, 'user_id' => $this->request->session()['user_id'] ] );
        $data['url'] = $url;

        $data['title'] = 'Страница актива';

        if ( !empty ( $data ) )
            extract($data);

        require_once 'app/view/page/share.php';
        return true;
    }



    /**
     * 
     * 
     */
    public function ideaCheckAjax () 
    {
        $this->load->model('', 'Stock');

        $error = [];

        $json = json_decode (file_get_contents("php://input"), true);

        $this->model_stock->addSecurityToIdea ($json);

        echo json_encode( [
            'error'  =>  $error, 
            'result' => '200'
        ], JSON_UNESCAPED_UNICODE );
    }



    /**
     * Ajax
     * 
     */
    public function updateDataAjax () 
    {
        $this->load->model('', 'Stock');

        $error = [];

        $json = json_decode (file_get_contents("php://input"), true);

        $assets = TRUE;

        if ( !empty ( $json['assets'] ) && !preg_match ( '~^[0-9\s]+$~iu', $json['assets'] ) ) 
        {
            $error['assets'] = 'Капитализация компании должна содержать только цифры';
            $assets = FALSE;
            echo json_encode( [
                'error'  =>  $error, 
                'result' => ''
            ], JSON_UNESCAPED_UNICODE );

            return;
        }
        
        $data = [
            'secid'        => $json['secid'],
            'assets'       => !empty ($json['assets']) && $assets ? preg_replace ('~\s+~iu', '', $json['assets']) : '', // Убираем пробелы 121 964 061 000,
            'actual_price' => $json['assets'] != 0 && $assets ? $json['assets'] / $json['issuesize'] : '',
            'portfolio_id' => $json['portfolio'],
            'user_id'      => $json['user_id']
        ];
        
        $result = $this->model_stock->updateSecurity ( $data );

        if ($result['result'] == true) 
        {
            if ( empty ($data['assets']) && empty ($data['portfolio_id']) ) 
                $this->model_stock->deleteSecurity ( $data );
            
            echo json_encode( [
                'error'  =>  $error, 
                'result' => [
                    'assets'       => $data['assets'],
                    'actual_price' => !empty ( $data['actual_price'] ) ? number_format ( $data['actual_price'], 2, ',', ' ' ) : ''
                ]
            ], JSON_UNESCAPED_UNICODE );
        }
        
    }



    /**
     * Полученные данные ложим в портфель
     * 
     */
    public function addPortfolioAjax () 
    {
        $this->load->model('', 'Stock');

        $json = json_decode ( file_get_contents("php://input"), true );
        $result = $this->model_stock->updateSecurity ($json);

        if ($result['result'] == true) 
            echo json_encode(['status' => 200]);

    }



    /**
     * 
     * 
     * 
     */
    public function getModalAjax () 
    {
        // Подключаем модели
        $this->load->model('', 'Stock');
        $this->load->model('', 'Portfolio');

        $data = [];

        $json = json_decode ( file_get_contents("php://input"), true );

        $data_sort = array_merge ($this->request->session (), $json);

        $data = $this->model_stock->getSecurity ( $data_sort );
        $data['user_id'] = $this->request->session ()['user_id'];
        $data['portfolios'] = $this->model_portfolio->getPortfolios ( $data_sort );

        extract ( $data );

        require_once 'app/view/tpl/modal.php';
        return true;
    }


}