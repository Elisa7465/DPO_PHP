--Создание таблицы книг
CREATE TABLE books (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    cover_path VARCHAR(255) NOT NULL,
    download_link VARCHAR(255),
    is_downloadable BOOLEAN NOT NULL DEFAULT false,
    read_date DATE NOT NULL DEFAULT CURRENT_DATE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
); 