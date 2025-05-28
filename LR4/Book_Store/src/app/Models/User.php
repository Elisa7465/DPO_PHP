<?php

namespace App\Models;

use App\Core\Database;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
//Модель пользователя
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // Идентификатор пользователя, может быть null если пользователь ещё не сохранён в базе
    private ?int $id = null;

    // Email пользователя (логин)
    private string $email;

    // Хэшированный пароль пользователя
    private string $password;

    // Роли пользователя в виде массива (например, ROLE_USER, ROLE_ADMIN)
    private array $roles = [];

    // Дата создания пользователя (формат строки), может быть null
    private ?string $created_at = null;

    // Конструктор, принимает email, пароль и роли (по умолчанию одна роль ROLE_USER)
    public function __construct(string $email = '', string $password = '', array $roles = ['ROLE_USER'])
    {
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles;
    }

    // Статический метод для поиска пользователя по email
    public static function findByEmail(string $email): ?self
    {
        $connection = Database::getConnection();
        // Выполняем SQL-запрос для получения данных пользователя по email
        $userData = $connection->fetchAssociative(
            'SELECT * FROM users WHERE email = ?',
            [$email]
        );

        // Если пользователь не найден, возвращаем null
        if (!$userData) {
            return null;
        }

        // Создаём объект User на основе полученных данных
        $user = new self($userData['email'], $userData['password']);
        $user->id = $userData['id'];
        // Роли хранятся в базе в формате JSON, декодируем их в массив
        $user->roles = json_decode($userData['roles'], true);
        $user->created_at = $userData['created_at'];

        return $user;
    }

    // Метод для сохранения пользователя в базе (создать или обновить)
    public function save(): bool
    {
        $connection = Database::getConnection();
        
        // Если id нет — новая запись, вставляем пользователя в таблицу
        if ($this->id === null) {
            $connection->insert('users', [
                'email' => $this->email,
                'password' => $this->password,
                // Сохраняем роли как JSON-строку
                'roles' => json_encode($this->roles)
            ]);

            // Получаем сгенерированный id и сохраняем его в объекте
            $this->id = $connection->lastInsertId();
            return true;
        }

        // Если id есть — обновляем запись пользователя по id
        $connection->update('users', [
            'email' => $this->email,
            'roles' => json_encode($this->roles)
        ], ['id' => $this->id]);

        return true;
    }

    // Геттер для id пользователя
    public function getId(): ?int
    {
        return $this->id;
    }

    // Геттер для email
    public function getEmail(): string
    {
        return $this->email;
    }

    // Геттер для даты создания пользователя
    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    // Геттер для ролей пользователя
    public function getRoles(): array
    {
        return $this->roles;
    }

    // Геттер для пароля (хэш)
    public function getPassword(): string
    {
        return $this->password;
    }

    // Метод, возвращающий соль для пароля, если используется (здесь нет — возвращаем null)
    public function getSalt(): ?string
    {
        return null;
    }

    // Метод, очищающий конфиденциальные данные (если есть), здесь пустой
    public function eraseCredentials(): void
    {
    }

    // Метод, возвращающий уникальный идентификатор пользователя для Symfony Security — здесь email
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    // Сеттер для email
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    // Сеттер для пароля (хэш)
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}
