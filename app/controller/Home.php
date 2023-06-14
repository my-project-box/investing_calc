<?php 

namespace controller;

use 
    engine\Controller,
    model\Modelstock;


class Home extends Controller 
{

    public function index () 
    {

        
        require_once 'app/view/page/shares.php';
        return true;

    }

}