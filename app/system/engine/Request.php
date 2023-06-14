<?php
namespace engine;

use Exception;

/**
 * Класс параметров
 * 
 */
class Request
{
   private $get;
   private $post;
   private $session;


   public function __construct()
   {
      $this->get     = $_GET;
      $this->post    = $_POST;
      $this->session = $_SESSION;
   }



   /**
    * GET
    *
    */
   public function get ( string $param = '')
   {
      if ( !empty ($this->get[$param]) )
         return $this->get[$param];

      return $this->get;
   }


   /**
    * POST
    *
    */
   public function post ()
   {
      return $this->post;
   }


   /**
    * SESSION
    *
    */
   public function session ()
   {
      return $this->session;
   }


} // End: class Request