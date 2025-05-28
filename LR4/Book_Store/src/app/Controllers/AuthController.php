<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Models\User;
use Twig\Environment;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
//Обработка аунтификации и авторизации
class AuthController
{
    // Объект Twig для рендеринга шаблонов
    private $twig;

    // Объект для хеширования и проверки паролей
    private $passwordHasher;

    // Конструктор принимает Twig и PasswordHasher через внедрение зависимостей
    public function __construct(Environment $twig, UserPasswordHasherInterface $passwordHasher)
    {
        $this->twig = $twig;
        $this->passwordHasher = $passwordHasher;
    }

    // Метод обработки логина пользователя
    public function login(Request $request): Response
    {
        // Проверяем, был ли запрос методом POST (форма отправлена)
        if ($request->isMethod('POST')) {
            // Получаем email и пароль из данных формы
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            
            // Ищем пользователя по email в базе
            $user = User::findByEmail($email);
            
            // Проверяем существует ли пользователь и корректен ли пароль
            if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
                // Если нет — возвращаем форму входа с сообщением об ошибке
                return new Response($this->twig->render('auth/login.twig', [
                    'error' => 'Неверный email или пароль'
                ]));
            }
            
            // Если всё успешно — сохраняем данные пользователя в сессии (для авторизации)
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_email'] = $user->getEmail();
            
            // Перенаправляем на главную страницу
            return new RedirectResponse('/');
        }

        // Если запрос не POST — просто показываем форму входа
        return new Response($this->twig->render('auth/login.twig'));
    }

    // Метод обработки регистрации нового пользователя
    public function register(Request $request): Response
    {
        // Проверяем, что форма отправлена методом POST
        if ($request->isMethod('POST')) {
            // Получаем email и пароль из формы
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            
            // Проверяем, есть ли уже пользователь с таким email
            if (User::findByEmail($email)) {
                // Если есть — возвращаем форму регистрации с сообщением об ошибке
                return new Response($this->twig->render('auth/register.twig', [
                    'error' => 'Пользователь с таким email уже существует'
                ]));
            }
            
            // Создаём нового пользователя
            $user = new User();
            $user->setEmail($email);
            
            // Хешируем пароль для безопасного хранения
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
            
            // Сохраняем пользователя в базе, если ошибка — показываем её
            if (!$user->save()) {
                return new Response($this->twig->render('auth/register.twig', [
                    'error' => 'Ошибка при регистрации'
                ]));
            }
            
            // После успешной регистрации сразу логиним пользователя (сохраняем в сессии)
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_email'] = $user->getEmail();
            
            // Перенаправляем на главную страницу
            return new RedirectResponse('/');
        }

        // Если запрос не POST — показываем форму регистрации
        return new Response($this->twig->render('auth/register.twig'));
    }

    // Метод для выхода пользователя (разлогинивания)
    public function logout(): Response
    {
        // Уничтожаем сессию — выходим из аккаунта
        session_destroy();

        // Перенаправляем на главную страницу
        return new RedirectResponse('/');
    }
}
