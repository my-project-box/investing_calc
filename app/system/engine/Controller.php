<?php 

namespace engine;

use 
   controller\User;

/**
 * 
 * 
 */
class Controller
{
   protected $registry;


   public function __construct($registry)
   {
      
      $this->registry = $registry;

      $user_check = false;
      $login      = isset ($this->request->post()['login']) ? $this->request->post()['login'] : '';
      $pass       = isset ($this->request->post()['password']) ? $this->request->post()['password'] : '';

      if ( empty ($login) || empty ($pass))
         $user_check = false;
      
      $user = new User ($registry);

      if ($login) {

         $user_data = $user->check ($login, $pass);
         
         if ($user_data) {
            $_SESSION['user_id'] = $user_data['user_id'];
            $_SESSION['role']    = $user_data['role'];
            $user_check = true;
         }
      }
      
      if ( isset ($this->request->session()['user_id']) )
         $user_check = true;

      if (!$user_check)
         $user->auth ();
   }


   /**
    * Используем "магический" метод __get для перенаправления к методу get()
    * класса регистратора (Registry)
    *
    */
   public function __get($key) 
   {
      return $this->registry->get($key);
   }


   /**
    * Используем "магический" метод __set для перенаправления к методу set()
    * класса регистратора (Registry)
    *
    */
   public function __set($key, $value) 
   {
      $this->registry->set($key, $value);
   }


} // End: Controller