# Используем официальный образ PHP версии 8.2 с предустановленным Apache сервером
FROM php:8.2-apache

# Обновляем список пакетов и устанавливаем библиотеку libpq-dev (нужна для работы с PostgreSQL)
RUN apt-get update && apt-get install -y libpq-dev

# Устанавливаем расширения PHP для работы с PostgreSQL:
# - pdo — универсальный интерфейс для работы с базами данных
# - pdo_pgsql — драйвер PDO для PostgreSQL
# - pgsql — расширение для прямой работы с PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Копируем все файлы из текущей директории (где находится Dockerfile)
# в директорию /var/www/html/ внутри контейнера — корневую папку веб-сервера Apache
COPY . /var/www/html/
