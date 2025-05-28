# Используем официальный образ PHP 8.2 с предустановленным Apache
FROM php:8.2-apache

# Обновляем список пакетов и устанавливаем необходимые библиотеки для работы с PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev

# Устанавливаем PHP-расширения для работы с PostgreSQL и PDO
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Копируем все файлы проекта (из текущей папки) в директорию веб-сервера контейнера
COPY . /var/www/html/
