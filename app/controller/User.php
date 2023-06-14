<?php 

namespace controller;

use 
    engine\Controller,
    model\Modelstock;


class User extends Controller 
{

    protected $registry;

    public function __construct($registry)
    {
        $this->registry = $registry; 
    }
    
    
    public function auth () 
    {
        
        $title = 'Авторизация';
        $button = 'Вход';

        require_once 'app/view/page/auth.php';
        die;
    }



    public function regist () 
    {
        if ( !empty ($this->request->post()['login']) && !empty ($this->request->post()['password'])) {
            
            // Подключаем модель
            $this->load->model('', 'User');
            $insert = $this->model_user->insert ( $this->request->post() );

            if (empty ($insert[1])) 
                header ('Location: /');
            
        }



        $regist = true;
        $title = 'Регистрация';
        $button = 'Отправить';

        require_once 'app/view/page/auth.php';
        return true;
    }



    public function check ($login, $pass) 
    {
        // Подключаем модель
        $this->load->model('', 'User');

        $data = $this->model_user->getDataAll($login);

        if ( isset ($data['password']) && password_verify ($pass, $data['password']))
            return ['user_id' => $data['id'], 'role' => $data['role']];

        return false;
    }

}