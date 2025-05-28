<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

session_start();

// Создаем необходимые директории при запуске
$uploadDirs = [
    __DIR__ . '/uploads',
    __DIR__ . '/uploads/books',
    __DIR__ . '/uploads/covers'
];

foreach ($uploadDirs as $dir) {
    if (!file_exists($dir)) {
        @mkdir($dir, 0777, true);
    }
}

$request = Request::createFromGlobals();
$pathInfo = $request->getPathInfo();

// Обработка статических файлов
if (preg_match('/^\/uploads\//', $pathInfo)) {
    $filePath = __DIR__ . $pathInfo;
    if (file_exists($filePath)) {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';
        header('Content-Type: ' . $contentType);
        readfile($filePath);
        exit;
    }
    
    header('HTTP/1.0 404 Not Found');
    exit;
}

$routes = require __DIR__ . '/../app/Config/routes.php';

// Загружаем контейнер зависимостей
$container = require __DIR__ . '/../app/Config/services.php';
$container->compile();

$context = new RequestContext();
$context->fromRequest($request);

$matcher = new UrlMatcher($routes, $context);

try {
    $parameters = $matcher->match($pathInfo);
    $controller = $parameters['_controller'];
    list($controllerClass, $method) = explode('::', $controller);
    
    // Получаем экземпляр контроллера из контейнера
    $controllerInstance = $container->get($controllerClass);
    
    // Добавляем параметры маршрута в атрибуты запроса
    foreach ($parameters as $key => $value) {
        if ($key !== '_controller' && $key !== '_route') {
            $request->attributes->set($key, $value);
        }
    }
    
    $response = $controllerInstance->$method($request);
    
    if (!$response instanceof Response) {
        $response = new Response($response);
    }
} catch (ResourceNotFoundException $e) {
    $response = new Response('Not Found', 404);
} catch (\Exception $e) {
    $response = new Response('Error: ' . $e->getMessage(), 500);
}

$response->send();
