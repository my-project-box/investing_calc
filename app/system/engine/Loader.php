<?php
namespace engine;

use Exception;

/**
 * Класс загрузки
 * 
 */
class Loader
{
   protected $registry;


   public function __construct($registry)
   {
      $this->registry = $registry;
   }



   /**
    * Метод загрузки моделей
    *
    */
   public function controller($pathToDir, $name)
   {
      $class = '\controller\\' . str_replace('/', '\\', mb_strtolower($pathToDir) . '/' . $name);

      if (!$this->registry->has($name)) {
      
         if (is_file(($path = ROOT. $class . '.php'))) {
            require_once $path;
            
            $this->registry->set('controller' . strtolower(preg_replace('/([A-Z0-9]+)/', '_$1', $name)), new $class($this->registry)); 
            return $this->registry->get('controller' . strtolower(preg_replace('/([A-Z0-9]+)/', '_$1', $name)));
         }
      } else {
         throw new Exception('Ошибка: не удалось загрузить контроллер' . $name. '!');
      }
   } // End: controller


   /**
    * Метод загрузки моделей
    *
    */
   public function model($pathToDirModel, $nameModel)
   {
      if (!empty($pathToDirModel)) {
         $class = 'model\\' . str_replace('/', '\\', mb_strtolower($pathToDirModel) . '/' .$nameModel);
      } else $class = 'model\\' . $nameModel;
      
      
      if (!$this->registry->has($nameModel)) {
         
         if (is_file(($path = ROOT_PATH . 'app/' . $class . '.php'))) {
            require_once $path;
            
            $this->registry->set('model' . strtolower(preg_replace('/([A-Z0-9]+)/', '_$1', $nameModel)), new $class($this->registry));
            
         }
      } else {
         throw new Exception('Ошибка: не удалось загрузить модель' . $nameModel. '!');
      }
      
   } // End: model


   /**
    * Метод загрузки внешнего вида
    *
    */
   public function view($path = '', $data = [])
   {
      $file = ROOT . '/view/' . $path . '.php';
      
      if (is_file($file)) {
         ob_start();
         extract($data);
         require_once $file;
         return ob_get_clean();
         
      } else die('Неправильный путь до файла внешнего вида страницы');

   } // End: view


} // End: class Loader