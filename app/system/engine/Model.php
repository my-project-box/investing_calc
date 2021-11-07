<?php
namespace engine;


/**
 * Класс моделей
 * 
 */
class Model
{
   protected $registry;

   
   public function __construct($registry)
   {
      //
      $this->registry = $registry;
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


} // End: class Model