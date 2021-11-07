<?php
namespace engine;

/**
 * Класс регистрации объектов
 * 
 */
final class Registry
{
   private $data = [];


   /**
    * Получаем объект
    * @param string $key
    * @return mixed
    */
   public function get($key)
   {
      return (isset($this->data[$key]) ? $this->data[$key] : null);
   }


   /**
    * Добавляем объект
    * @param string $key
    * @param string $value
    */
    public function set($key, $value)
    {
      $this->data[$key] = $value;
    }


    /**
    * Проверяем на существование объекта
    * @param string $key
    * @return bool
    */
    public function has($key)
    {
      return isset($this->data[$key]);
    }

} // End: class Registry