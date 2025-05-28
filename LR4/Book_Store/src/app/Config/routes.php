<?php

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

// Создаём коллекцию маршрутов для приложения
$routes = new RouteCollection();

// Главная страница сайта
$routes->add('home', new Route('/', [
    // Указываем контроллер и метод, который будет обрабатывать этот маршрут
    '_controller' => 'App\Controllers\HomeController::index',
], [], [], '', [], ['GET'])); // Разрешён только HTTP метод GET

// Маршрут для страницы входа (логина)
$routes->add('login', new Route('/login', [
    '_controller' => 'App\Controllers\AuthController::login',
], [], [], '', [], ['GET', 'POST'])); // Разрешены методы GET (форма) и POST (отправка)

// Маршрут для страницы регистрации пользователя
$routes->add('register', new Route('/register', [
    '_controller' => 'App\Controllers\AuthController::register',
], [], [], '', [], ['GET', 'POST'])); // Методы GET и POST

// Маршрут для выхода пользователя из системы (логаут)
$routes->add('logout', new Route('/logout', [
    '_controller' => 'App\Controllers\AuthController::logout',
], [], [], '', [], ['GET'])); // Только GET запрос

// Маршрут для создания новой книги
$routes->add('books_create', new Route('/books/create', [
    '_controller' => 'App\Controllers\BookController::create',
], [], [], '', [], ['GET', 'POST'])); // GET для формы, POST для сохранения

// Маршрут для редактирования книги по её ID
$routes->add('books_edit', new Route('/books/{id}/edit', [
    '_controller' => 'App\Controllers\BookController::edit',
], ['id' => '\d+'], [], '', [], ['GET', 'POST'])); 
// Параметр {id} должен быть числом (\d+)
// Методы GET (форма) и POST (обновление)

// Маршрут для удаления книги по ID
$routes->add('books_delete', new Route('/books/{id}/delete', [
    '_controller' => 'App\Controllers\BookController::delete',
], ['id' => '\d+'], [], '', [], ['GET', 'POST'])); 
// Также с параметром id - число
// Методы GET и POST разрешены

// Возвращаем все маршруты в виде коллекции
return $routes;
