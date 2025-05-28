<?php

namespace App\Core;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class Database
{
    // Статическое свойство для хранения единственного подключения к базе (паттерн Singleton)
    private static ?Connection $connection = null;

    // Метод для получения объекта подключения к базе данных
    public static function getConnection(): Connection
    {
        // Если подключения ещё нет — создаём его
        if (self::$connection === null) {
            // Загружаем конфигурацию подключения из файла config/database.php
            $config = require __DIR__ . '/../Config/database.php';

            // Создаём подключение через Doctrine DBAL по конфигурации
            self::$connection = DriverManager::getConnection($config);
        }

        // Возвращаем объект подключения (один и тот же для всех вызовов)
        return self::$connection;
    }
}
