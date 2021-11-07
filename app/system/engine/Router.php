<?php
namespace engine;

class Router 
{
	private $routes;
	protected $registry;
	
	public function __construct($registry)
	{
	   $routerPatch = ROOT . '/app/routes.php';
		$this->routes = require_once($routerPatch);

		$this->registry = $registry;
	   
	} // End: function __construct
	
	
	/**
	* Возвращаем строку
	*/
	private function getUri()
	{
		if(!empty($_SERVER['REQUEST_URI']))
		{
			return trim($_SERVER['REQUEST_URI'], '/');
		}
	} // End: function getUri
	
	
	public function run()
	{
		// Получаем строку запроса
		$uri = $this->getUri();
		$error = true;
		
		// Проверяем наличие запроса в config.php
		foreach ($this->routes as $uriPattern => $path) {
			// Сравниваем $uriPattern и $uri
			if (preg_match("~$uriPattern~", $uri)) {

				$error = false;

				// Получаем внутрений путь из внешнего согласно правилу
				$externalPath = preg_replace("~$uriPattern~", $path, $uri);
				
				// Определяем Controller и action
				$segments = explode (':', $externalPath);
				//preg_match_all('/[a-z][^A-Z]*?|[A-Z][^A-Z]*?/Us', array_shift($segments), $matches);
				$matches = explode('/', array_shift($segments));
				// Получаем имя Controller
				$controllerName = ucfirst(array_pop($matches)).'Controller';
				// Получаем имя метода
				$metodName = ucfirst(array_shift($segments));
				// Получаем параметры
				$parameters = $segments;
				// Получаем путь до контроллера
				$pathController = '\controllers\\';
				
				if (!empty($matches)) {
					foreach ($matches as $item) {
						$pathController .= str_replace('_', '\\', strtolower($item)) . '\\';
					}

				} //else $pathController .= 'catalog\\page\\';
				
				$pathController .= $controllerName;
				
				// Проверяем контроллер
				if (class_exists($pathController)) {
					
					// Подключаем класс контроллера
					// Создаем объект и вызываем метод action...
					$controllerObject = new $pathController($this->registry);
					
					// Проверяет, существует ли метод в данном классе
					if(method_exists($controllerObject, $metodName))
						$result = call_user_func_array (array($controllerObject, $metodName), $parameters);
					else die('Такой метод не существует! Дружище не забудь здесь установить страницу 404')/*header("Location: /")*/;
					
					if ($result != NULL) break;

				} else die('Такой контроллер не существует! Дружище не забудь здесь установить страницу 404');

			}
		}

		if ($error) die ('Дружище не забудь здесь установить страницу 40444');

	} // End: function run
	
} // End: Class Router