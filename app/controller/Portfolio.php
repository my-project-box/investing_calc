<?php

namespace controller;

use 
    engine\Controller,
    model\Modelstock;



class Portfolio extends Controller 
{

    public function index ($action = '') 
    {
        // Подключаем модели
        $this->load->model('', 'Portfolio');

        $data = [];
        
        // Создаем портфель
        if (isset ($this->request->post()['user_id'])) 
        {
            $this->model_portfolio->insert($this->request->post());
            // обновление страницы
            header("Location: ".$_SERVER['REQUEST_URI']);
        }
        
        // Удаляем портфель
        if ($action == 'delete') 
        {
            $data_sort = [
                'portfolio_id' => $this->request->get()['id'],
                'user_id'      => $this->request->session()['user_id'],
            ];

            $this->model_portfolio->delete(  $data_sort );
            $this->model_portfolio->deleteAssets ( $data_sort );
            header("Location: /portfolio");
        }

        $data['portfolios'] = $this->model_portfolio->getPortfolios($this->request->session());
        $data['assets'] = [];

        $data_sort = $this->request->session();

        foreach ($data['portfolios'] as $portfolio) 
            $data_sort['portfolios_id'][] = $portfolio['id'];

        $data['assets'] = $this->model_portfolio->getAssetsByPortfolioId($data_sort);
        
        $data['title']                  = 'Страница портфелей ценных бумаг';
        $data['title_page']             = 'Портфели';
        $data['text_info']              = 'Пока нет ниодного портфеля у вас';
        $data['user_id']                = $this->request->session()['user_id'];
        $data['portfolio_name_default'] = 'Новый портфель № ';
        $data['portfolio_info_default'] = 'Портфель пока пуст!';

        $script = '
            <script src="../app/view/js/portfolio.js"></script>
            <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        ';

        extract($data);

        require_once 'app/view/page/portfolio.php';
        return true;

    }



    /**
     * Редактируем имя портфеля
     * 
     */
    public function editNameAjax () 
    {
        // Подключаем модели
        $this->load->model('', 'Portfolio');

        $data = array_merge ( json_decode( file_get_contents ('php://input'), true ), $this->request->session () );
        $response = $this->model_portfolio->insert($this->request->post());

        if ($response['countString'])
            echo json_encode( ['code' => '200'], JSON_UNESCAPED_UNICODE );
        else
            echo json_encode( ['code' => '404'], JSON_UNESCAPED_UNICODE );
    }



    /**
     * Удаляем из портфеля актив
     * 
     */
    public function removeAssetsFromPortfolioAjax () 
    {
        // Подключаем модели
        $this->load->model('', 'Portfolio');

        $data = array_merge ( json_decode( file_get_contents ('php://input'), true ), $this->request->session () );
        $response = $this->model_portfolio->deleteAsset ( $data );

        if ($response['countString'])
            echo json_encode( ['code' => '200'], JSON_UNESCAPED_UNICODE );
        else
            echo json_encode( ['code' => '404'], JSON_UNESCAPED_UNICODE );
    }

}