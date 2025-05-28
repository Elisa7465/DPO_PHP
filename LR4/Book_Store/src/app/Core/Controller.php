<?php

namespace App\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class Controller
{
    // Объект для работы с шаблонами Twig
    protected Environment $twig;

    // Объект запроса HTTP (содержит данные из GET, POST, COOKIE и т.д.)
    protected Request $request;

    // Конструктор контроллера
    public function __construct()
    {
        // Указываем папку с шаблонами Twig (относительно текущей директории)
        $loader = new FilesystemLoader(__DIR__ . '/../../views');

        // Создаём объект Twig Environment для рендеринга шаблонов
        $this->twig = new Environment($loader);

        // Создаём объект запроса из глобальных переменных PHP ($_GET, $_POST, $_COOKIE и др.)
        $this->request = Request::createFromGlobals();
    }

    // Метод для рендеринга HTML-страницы из шаблона Twig с передачей параметров
    // Возвращает объект Response с готовым HTML-контентом
    protected function render(string $template, array $params = []): Response
    {
        // Генерируем HTML из шаблона с данными
        $content = $this->twig->render($template, $params);

        // Создаём и возвращаем HTTP-ответ с содержимым HTML
        return new Response($content);
    }

    // Метод для формирования JSON-ответа с данными и статусом HTTP
    protected function json(array $data, int $status = 200): Response
    {
        return new Response(
            json_encode($data),                 // Преобразуем массив в JSON-строку
            $status,                           // HTTP статус (например, 200 OK)
            ['Content-Type' => 'application/json'] // Заголовок, что ответ JSON
        );
    }

    // Метод для перенаправления на другой URL (редирект)
    protected function redirect(string $url): Response
    {
        // Возвращаем ответ с кодом 302 и заголовком Location для перенаправления браузера
        return new Response('', 302, ['Location' => $url]);
    }
}
