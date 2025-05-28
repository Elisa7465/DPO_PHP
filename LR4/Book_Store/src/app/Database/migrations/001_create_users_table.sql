--создание таблицы пользователей
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    roles JSON NOT NULL DEFAULT '["ROLE_USER"]',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 