<?php

namespace App\Controllers;

use App\Models\Book;
use App\Models\User;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
//Обработка главной страницы
class HomeController
{
    // Объект Twig для рендеринга шаблонов
    private $twig;

    // Конструктор принимает Twig через внедрение зависимостей
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    // Метод для отображения главной страницы
    public function index(): Response
    {
        // Получаем список всех книг из базы данных
        $books = Book::findAll();

        // Проверяем, авторизован ли пользователь (есть ли user_id в сессии)
        $is_authenticated = isset($_SESSION['user_id']);
        $user = null;

        // Если пользователь авторизован, загружаем его данные по email из сессии
        if ($is_authenticated) {
            $user = User::findByEmail($_SESSION['user_email']);
        }

        // Рендерим шаблон главной страницы, передавая туда данные:
        // книги, статус авторизации, данные пользователя и сообщения из сессии
        $content = $this->twig->render('home/index.twig', [
            'books' => $books,
            'is_authenticated' => $is_authenticated,
            'user' => $user,
            'success' => $_SESSION['success'] ?? null, // Сообщение об успехе
            'error' => $_SESSION['error'] ?? null      // Сообщение об ошибке
        ]);

        // Очищаем сообщения в сессии, чтобы они не повторялись после обновления страницы
        unset($_SESSION['success'], $_SESSION['error']);

        // Возвращаем HTTP-ответ с готовым HTML-контентом
        return new Response($content);
    }
}
