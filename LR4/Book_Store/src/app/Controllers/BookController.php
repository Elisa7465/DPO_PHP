<?php

namespace App\Controllers;

use App\Models\Book;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//Обработка для книг
class BookController
{
    private $twig;

    // Разрешённые расширения для обложки книги
    private const ALLOWED_COVER_TYPES = ['jpg', 'jpeg', 'png'];

    // Максимальный размер файла книги — 5 мегабайт
    private const MAX_BOOK_SIZE = 5 * 1024 * 1024; // 5MB

    // Папка для загрузки файлов (обложек и книг)
    private const UPLOAD_DIR = 'public/uploads';

    // Конструктор принимает Twig для рендеринга шаблонов
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    // Метод создания новой книги
    public function create(Request $request): Response
    {
        // Проверяем, что пользователь авторизован (есть user_id в сессии)
        if (!isset($_SESSION['user_id'])) {
            // Если нет — перенаправляем на страницу логина
            return new Response('', 302, ['Location' => '/login']);
        }

        $error = null;  // Для хранения сообщений об ошибках
        $old = [];      // Для хранения старых значений формы (чтобы заполнить форму при ошибке)

        // Если форма была отправлена методом POST — обрабатываем данные
        if ($request->isMethod('POST')) {
            try {
                // Получаем и обрезаем пробелы в данных из формы
                $title = trim($request->request->get('title', ''));
                $author = trim($request->request->get('author', ''));
                $readDate = trim($request->request->get('read_date', ''));
                $isDownloadable = (bool)$request->request->get('is_downloadable', false);
                
                // Сохраняем данные формы для повторного вывода в случае ошибки
                $old = $request->request->all();

                // Проверяем, что обязательные поля заполнены
                if (empty($title) || empty($author) || empty($readDate)) {
                    throw new \RuntimeException('Все поля должны быть заполнены');
                }

                // Проверяем корректность формата даты (год-месяц-день)
                $dateTime = \DateTime::createFromFormat('Y-m-d', $readDate);
                if (!$dateTime) {
                    throw new \RuntimeException('Неверный формат даты');
                }

                // Получаем загруженные файлы из формы
                $coverFile = $request->files->get('cover');
                $bookFile = $request->files->get('book_file');

                // Проверяем, что обложка загружена
                if (!$coverFile) {
                    throw new \RuntimeException('Необходимо загрузить обложку книги');
                }

                // Проверяем расширение файла обложки на допустимые форматы
                $coverExt = strtolower($coverFile->getClientOriginalExtension());
                if (!in_array($coverExt, self::ALLOWED_COVER_TYPES)) {
                    throw new \RuntimeException('Загрузите обложку в формате JPG или PNG');
                }

                // Если файл книги загружен — проверяем его размер
                if ($bookFile && $bookFile->getSize() > self::MAX_BOOK_SIZE) {
                    throw new \RuntimeException('Размер файла книги не должен превышать 5MB');
                }

                // Сохраняем файлы и получаем пути к ним
                $coverPath = $this->processUploadedFile($coverFile, 'covers');
                $bookPath = null;
                
                if ($bookFile) {
                    $bookPath = $this->processUploadedFile($bookFile, 'books');
                }

                // Создаём объект книги и заполняем его данными
                $book = new Book();
                $book->setTitle($title);
                $book->setAuthor($author);
                $book->setCoverPath($coverPath);
                $book->setDownloadLink($bookPath);
                $book->setIsDownloadable($isDownloadable);
                $book->setReadDate($dateTime);

                // Сохраняем книгу в базу данных, если ошибка — удаляем загруженные файлы
                if (!$book->save()) {
                    $this->removeUploadedFile($coverPath);
                    if ($bookPath) {
                        $this->removeUploadedFile($bookPath);
                    }
                    throw new \RuntimeException('Ошибка при сохранении книги в базу данных');
                }

                // При успешном добавлении устанавливаем сообщение в сессию и редиректим на главную
                $_SESSION['success'] = 'Книга успешно добавлена';
                return new Response('', 302, ['Location' => '/']);

            } catch (\Exception $e) {
                // Логируем ошибку и сохраняем её для отображения пользователю
                error_log("Ошибка при создании книги: " . $e->getMessage());
                $error = $e->getMessage();
            }
        }

        // Рендерим форму создания книги с ошибкой (если есть) и старыми данными
        return new Response($this->twig->render('books/create.twig', [
            'error' => $error,
            'old' => $old,
            'max_file_size' => self::MAX_BOOK_SIZE
        ]));
    }

    // Метод редактирования существующей книги
    public function edit(Request $request): Response
    {
        // Проверка авторизации пользователя
        if (!isset($_SESSION['user_id'])) {
            return new Response('', 302, ['Location' => '/login']);
        }

        // Получаем id книги из параметров запроса и проверяем корректность
        $id = (int)$request->attributes->get('id');
        if ($id <= 0) {
            $_SESSION['error'] = 'Некорректный идентификатор книги';
            return new Response('', 302, ['Location' => '/']);
        }

        // Получаем книгу из базы по id
        $book = Book::findById($id);
        if (!$book) {
            $_SESSION['error'] = 'Книга не найдена';
            return new Response('', 302, ['Location' => '/']);
        }

        $error = null;
        $old = $request->request->all();

        // Обработка POST-запроса с изменениями
        if ($request->isMethod('POST')) {
            try {
                // Получаем новые данные из формы и обрезаем пробелы
                $title = trim($request->request->get('title', ''));
                $author = trim($request->request->get('author', ''));
                $readDate = trim($request->request->get('read_date', ''));
                $isDownloadable = (bool)$request->request->get('is_downloadable', false);

                // Проверяем, нужно ли удалить обложку или файл книги
                $removeCover = $request->request->has('remove_cover');
                $removeBook = $request->request->has('remove_book');

                // Проверяем обязательные поля
                if (empty($title) || empty($author) || empty($readDate)) {
                    throw new \RuntimeException('Все поля должны быть заполнены');
                }

                // Проверяем корректность даты
                $dateTime = \DateTime::createFromFormat('Y-m-d', $readDate);
                if (!$dateTime) {
                    throw new \RuntimeException('Неверный формат даты');
                }

                // Обработка обложки книги
                if ($removeCover) {
                    // Если нужно удалить обложку — удаляем файл с диска и очищаем путь
                    if ($book->getCoverPath() && file_exists('public/' . $book->getCoverPath())) {
                        @unlink('public/' . $book->getCoverPath());
                    }
                    $book->setCoverPath('');
                } else {
                    // Если загружена новая обложка — проверяем и сохраняем
                    $coverFile = $request->files->get('cover');
                    if ($coverFile) {
                        $extension = strtolower($coverFile->getClientOriginalExtension());
                        if (!in_array($extension, self::ALLOWED_COVER_TYPES)) {
                            throw new \RuntimeException('Загрузите обложку в формате JPG или PNG');
                        }

                        // Удаляем старую обложку
                        if ($book->getCoverPath() && file_exists($book->getCoverPath())) {
                            @unlink($book->getCoverPath());
                        }

                        // Сохраняем новую обложку и обновляем путь
                        $coverPath = $this->processUploadedFile($coverFile, 'covers');
                        $book->setCoverPath($coverPath);
                    }
                }

                // Обработка файла книги
                if ($removeBook) {
                    // Если удаляем файл книги — удаляем файл и очищаем ссылку, а также флаг скачивания
                    if ($book->getDownloadLink() && file_exists($book->getDownloadLink())) {
                        @unlink($book->getDownloadLink());
                    }
                    $book->setDownloadLink(null);
                    $book->setIsDownloadable(false);
                } else {
                    // Если загружен новый файл книги — проверяем и сохраняем
                    $bookFile = $request->files->get('book_file');
                    if ($bookFile) {
                        if ($bookFile->getSize() > self::MAX_BOOK_SIZE) {
                            throw new \RuntimeException('Размер файла книги не должен превышать 5MB');
                        }

                        // Удаляем старый файл книги
                        if ($book->getDownloadLink() && file_exists($book->getDownloadLink())) {
                            @unlink($book->getDownloadLink());
                        }

                        // Сохраняем новый файл книги и обновляем ссылку
                        $bookPath = $this->processUploadedFile($bookFile, 'books');
                        $book->setDownloadLink($bookPath);
                    }
                }

                // Обновляем остальные данные книги
                $book->setTitle($title);
                $book->setAuthor($author);
                $book->setReadDate($dateTime);
                $book->setIsDownloadable($isDownloadable);

                // Сохраняем изменения в базе
                if (!$book->save()) {
                    throw new \RuntimeException('Ошибка при сохранении книги');
                }

                // Устанавливаем сообщение об успехе и редиректим на главную
                $_SESSION['success'] = 'Книга успешно обновлена';
                return new Response('', 302, ['Location' => '/']);

            } catch (\Exception $e) {
                // Запоминаем ошибку для показа пользователю
                $error = $e->getMessage();
            }
        }

        // Рендерим форму редактирования с ошибкой (если есть), данными книги и старыми значениями
        return new Response($this->twig->render('books/edit.twig', [
            'error' => $error,
            'book' => $book,
            'old' => $old,
            'max_file_size' => self::MAX_BOOK_SIZE
        ]));
    }

    // Метод удаления книги
    public function delete(Request $request): Response
    {
        // Проверка авторизации
        if (!isset($_SESSION['user_id'])) {
            return new Response('', 302, ['Location' => '/login']);
        }

        // Получаем id книги и проверяем корректность
        $id = (int)$request->attributes->get('id');
        if ($id <= 0) {
            $_SESSION['error'] = 'Некорректный идентификатор книги';
            return new Response('', 302, ['Location' => '/']);
        }

        // Получаем книгу по id
        $book = Book::findById($id);
        if (!$book) {
            $_SESSION['error'] = 'Книга не найдена';
            return new Response('', 302, ['Location' => '/']);
        }

        try {
            // Запоминаем пути к файлам для удаления после удаления из базы
            $coverPath = $book->getCoverPath();
            $bookPath = $book->getDownloadLink();

            // Удаляем запись из базы
            if ($book->delete()) {
                // Удаляем файлы обложки и книги, если они существуют
                if ($coverPath && file_exists('public/' . $coverPath)) {
                    @unlink('public/' . $coverPath);
                }
                if ($bookPath && file_exists('public/' . $bookPath)) {
                    @unlink('public/' . $bookPath);
                }
                $_SESSION['success'] = 'Книга успешно удалена';
            } else {
                throw new \RuntimeException('Ошибка при удалении книги');
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Ошибка при удалении книги: ' . $e->getMessage();
        }

        // Редирект на главную страницу
        return new Response('', 302, ['Location' => '/']);
    }

    // Вспомогательный метод для сохранения загруженного файла
    private function processUploadedFile($file, string $type): string
    {
        // Проверяем корректность файла
        if (!$file || !$file->isValid()) {
            throw new \RuntimeException('Ошибка загрузки файла');
        }

        // Получаем расширение файла в нижнем регистре
        $extension = strtolower($file->getClientOriginalExtension());

        // Генерируем уникальное имя файла, используя md5 и uniqid
        $filename = md5(uniqid() . $file->getClientOriginalName() . time()) . '.' . $extension;

        // Относительный путь к файлу (для хранения в базе)
        $relativePath = "public/uploads/{$type}/{$filename}";

        // Абсолютный путь на сервере для сохранения файла
        $fullPath = self::UPLOAD_DIR . "/{$type}/{$filename}";

        try {
            // Перемещаем загруженный файл в нужную директорию
            $file->move(self::UPLOAD_DIR . "/{$type}", $filename);
            return $relativePath;
        } catch (\Exception $e) {
            // Логируем ошибку и выбрасываем исключение с понятным сообщением
            error_log("Ошибка сохранения файла: " . $e->getMessage());
            throw new \RuntimeException('Ошибка при сохранении файла. Проверьте права доступа.');
        }
    }

    // Вспомогательный метод для удаления файла с сервера по пути
    private function removeUploadedFile(?string $path): void
    {
        // Если путь пустой — ничего не делаем
        if (empty($path)) {
            return;
        }

        // Преобразуем путь, убирая 'public/' (если нужно)
        $fullPath = str_replace('public/', '', $path);

        // Проверяем существует ли файл и является ли он файлом (не директорией)
        if (file_exists($fullPath) && is_file($fullPath)) {
            @unlink($fullPath); // Удаляем файл, @ чтобы подавить предупреждения
        }
    }
}
