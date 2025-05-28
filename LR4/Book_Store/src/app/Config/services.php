<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;//Для хэширования паролей
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

// Создаём контейнер для управления зависимостями (Dependency Injection Container)
$containerBuilder = new ContainerBuilder();

// Регистрируем загрузчик шаблонов Twig, указываем папку с шаблонами
$containerBuilder->register('twig.loader', FilesystemLoader::class)
    ->addArgument(__DIR__ . '/../../views');

// Регистрируем сам Twig Environment, зависимость — загрузчик шаблонов
$containerBuilder->register('twig', Environment::class)
    ->addArgument(new Reference('twig.loader'));

// Регистрируем фабрику для хеширования паролей с настройками:
// алгоритм "auto" и стоимость вычисления 12 (чем выше — тем медленнее и безопаснее)
$containerBuilder->register('password.hasher.factory', PasswordHasherFactory::class)
    ->addArgument([
        PasswordAuthenticatedUserInterface::class => [
            'algorithm' => 'auto',
            'cost' => 12
        ]
    ]);

// Регистрируем объект хешера паролей, который будет использовать фабрику
$containerBuilder->register('password.hasher', UserPasswordHasher::class)
    ->addArgument(new Reference('password.hasher.factory'));

// Регистрируем контроллер HomeController, передавая ему Twig через конструктор
$containerBuilder->register(App\Controllers\HomeController::class)
    ->addArgument(new Reference('twig'))
    ->setPublic(true); // Контроллер доступен извне контейнера

// Регистрируем AuthController с Twig и PasswordHasher, доступен публично
$containerBuilder->register(App\Controllers\AuthController::class)
    ->addArgument(new Reference('twig'))
    ->addArgument(new Reference('password.hasher'))
    ->setPublic(true);

// Регистрируем BookController с Twig, тоже публичный
$containerBuilder->register(App\Controllers\BookController::class)
    ->addArgument(new Reference('twig'))
    ->setPublic(true);

// Возвращаем сконфигурированный контейнер для использования в приложении
return $containerBuilder;
