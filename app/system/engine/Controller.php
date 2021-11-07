<?php 
namespace engine;

use 
   models\user\User,
   system\classes\SiteFunction;

/**
 * 
 * 
 */
class Controller
{
   protected $registry;

   public function __construct($registry)
   {
      
      // Проверка доступа
      /*if (strpos($_SERVER['REQUEST_URI'], 'admin')) {
         
         $registry->set('user', new User($registry));
         
         // http://vitrina.loc/admin/dashboard/product_image_by_url?email=tst@test.ru&password=123456
         if (isset($_GET['email']) && isset($_GET['password'])) {
            // Если форма отправлена 
            // Получаем данные из формы
            $email    = SiteFunction::sanitizeString($_GET['email']);
            $password = SiteFunction::sanitizeString($_GET['password']);

            // Проверяем существует ли пользователь
            $user = false;
            $user = User::checkUserData($email, $password);

            if ($user) {
                  // Если данные правильные, запоминаем пользователя (сессия)
               User::auth($user);

               $parse_url = parse_url($_SERVER['REQUEST_URI']);
               header('Location: ' . $parse_url['path']);

            } else die('Access denied');
         }

         // Подключаем модель
         $registry->get('user')->check();
         
      }*/
      
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


} // End: Controller