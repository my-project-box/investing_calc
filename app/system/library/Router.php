<?php
namespace library;

class Router 
{
	private $routes;
	protected $registry;
	
	public function __construct($registry)
	{
	   	if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/setting')) {
			   mkdir($_SERVER['DOCUMENT_ROOT'] . '/setting');
		}

		if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/setting/routes.php')) {
			die('Файл с маршрутами в директории "Setting" не существует!');
		}

		$routerPatch = $_SERVER['DOCUMENT_ROOT'] . '/setting/routes.php';

		if (empty($this->routes = require_once($routerPatch))) {
			die ('В файле маршрутов нет маршрутов!');
		}
		
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
		
		// Проверяем наличие запроса в config.php
		foreach ($this->routes as $uriPattern => $path)
		{
			// Сравниваем $uriPattern и $uri
			if (preg_match("~$uriPattern~", $uri))
			{
				// Получаем внутрений путь из внешнего согласно правилу
				$externalPath = preg_replace("~$uriPattern~", $path, $uri);
				// Определяем Controller и action
				$segments = explode (':', $externalPath);
				//preg_match_all('/[a-z][^A-Z]*?|[A-Z][^A-Z]*?/Us', array_shift($segments), $matches);
				$matches = explode('/', array_shift($segments));
				// Получаем имя Controller
				$controllerName = ucfirst(array_pop($matches));
				// Получаем метод контроллера
				$metodName = array_shift($segments);
				// Получаем get параметры
				$parameters = $segments;
				// Получаем путь до контроллера
				$pathController = '\controller\\';
				
				if (!empty($matches)) {
					foreach ($matches as $item) {
						$pathController .= strtolower($item) . '\\';
					}

				} //else $pathController .= 'catalog\\';

				$pathController .= $controllerName;
				
				// Подключаем класс контроллера
				if (class_exists($pathController)) {
					// Создаем объект и вызываем метод action...
					$controllerObject = new $pathController($this->registry);
					
					// Проверяет, существует ли метод в данном классе
					if(method_exists($controllerObject, $metodName))
						$result = call_user_func_array (array($controllerObject, $metodName), $parameters);
					else die('Такой метод не существует! Дружище не забудь здесь установить страницу 404')/*header("Location: /")*/;
					
					if ($result != NULL) break;
				} else die('Такой контроллер не существует! Дружище не забудь здесь установить страницу 404');

				break;
			}
		}
	} // End: function run
	
} // End: Class Router