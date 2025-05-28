<?php

namespace App\Models;

use App\Core\Database;
//Модель Книги
class Book
{
    // Идентификатор книги, может быть null если книга ещё не сохранена в базе
    private ?int $id = null;

    // Название книги
    private string $title;

    // Автор книги
    private string $author;

    // Путь к обложке книги (например, путь к файлу или URL)
    private string $cover_path;

    // Ссылка для скачивания книги, может отсутствовать (null)
    private ?string $download_link;

    // Флаг, можно ли скачивать книгу (true или false)
    private bool $is_downloadable;

    // Дата прочтения книги, объект DateTime
    private \DateTime $read_date;

    // Дата создания записи о книге в базе (формат строки)
    private string $created_at;

    // Конструктор класса, задаёт свойства книги, если значения не переданы, ставит значения по умолчанию
    public function __construct(
        string $title = '',
        string $author = '',
        string $cover_path = '',
        ?string $download_link = null,
        bool $is_downloadable = false,
        ?\DateTime $read_date = null
    ) {
        $this->title = $title;
        $this->author = $author;
        $this->cover_path = $cover_path;
        $this->download_link = $download_link;
        $this->is_downloadable = $is_downloadable;
        // Если дата прочтения не передана, ставим текущую дату
        $this->read_date = $read_date ?? new \DateTime();
    }

    // Статический метод для получения всех книг из базы данных
    public static function findAll(): array
    {
        // Получаем соединение с базой данных
        $connection = Database::getConnection();

        // Выполняем SQL-запрос на выборку всех книг, сортируя по дате прочтения по убыванию
        $stmt = $connection->executeQuery('SELECT * FROM books ORDER BY read_date DESC');
        
        $books = [];
        // Проходим по каждой записи результата запроса
        while ($row = $stmt->fetchAssociative()) {
            // Создаём объект книги на основе данных из строки
            $book = new self(
                $row['title'],
                $row['author'],
                $row['cover_path'],
                $row['download_link'],
                (bool)$row['is_downloadable'],
                new \DateTime($row['read_date'])
            );
            // Устанавливаем id и дату создания из базы
            $book->id = $row['id'];
            $book->created_at = $row['created_at'];
            // Добавляем объект книги в массив результата
            $books[] = $book;
        }
        
        return $books;
    }

    // Статический метод для поиска книги по её id
    public static function findById(int $id): ?self
    {
        // Получаем соединение с базой данных
        $connection = Database::getConnection();

        // Выполняем запрос на выборку книги по id
        $row = $connection->fetchAssociative('SELECT * FROM books WHERE id = ?', [$id]);
        
        // Если книга с таким id не найдена, возвращаем null
        if (!$row) {
            return null;
        }

        // Создаём объект книги из данных строки
        $book = new self(
            $row['title'],
            $row['author'],
            $row['cover_path'],
            $row['download_link'],
            (bool)$row['is_downloadable'],
            new \DateTime($row['read_date'])
        );
        $book->id = $row['id'];
        $book->created_at = $row['created_at'];
        
        return $book;
    }

    // Метод для сохранения книги в базе данных (создать или обновить)
    public function save(): bool
    {
        $connection = Database::getConnection();
        
        // Формируем массив данных для вставки/обновления
        $data = [
            'title' => $this->title,
            'author' => $this->author,
            'cover_path' => $this->cover_path,
            'download_link' => $this->download_link,
            // Сохраняем как строку 'true' или 'false'
            'is_downloadable' => $this->is_downloadable ? 'true' : 'false',
            // Форматируем дату прочтения в строку для базы
            'read_date' => $this->read_date->format('Y-m-d')
        ];

        // Если id нет — это новая книга, выполняем вставку
        if ($this->id === null) {
            $result = $connection->insert('books', $data);
            if ($result > 0) {
                // Получаем сгенерированный id и присваиваем объекту
                $this->id = (int)$connection->lastInsertId();
                return true;
            }
            return false;
        }

        // Если id есть — обновляем существующую запись по id
        return $connection->update('books', $data, ['id' => $this->id]) > 0;
    }

    // Метод для удаления книги из базы
    public function delete(): bool
    {
        // Если id нет — удалять нечего, возвращаем false
        if ($this->id === null) {
            return false;
        }

        $connection = Database::getConnection();

        // Удаляем запись из базы по id и возвращаем true, если было удалено более 0 строк
        return $connection->delete('books', ['id' => $this->id]) > 0;
    }

    // Ниже — геттеры для получения значений свойств объекта

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getCoverPath(): string
    {
        return $this->cover_path;
    }

    public function getDownloadLink(): ?string
    {
        return $this->download_link;
    }

    public function isDownloadable(): bool
    {
        return $this->is_downloadable;
    }

    public function getReadDate(): \DateTime
    {
        return $this->read_date;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    // Ниже — сеттеры для изменения значений свойств объекта

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    public function setCoverPath(string $cover_path): void
    {
        $this->cover_path = $cover_path;
    }

    public function setDownloadLink(?string $download_link): void
    {
        $this->download_link = $download_link;
    }

    public function setIsDownloadable(bool $is_downloadable): void
    {
        $this->is_downloadable = $is_downloadable;
    }

    public function setReadDate(\DateTime $read_date): void
    {
        $this->read_date = $read_date;
    }
}
