-- Добавление колонки read_date
ALTER TABLE books ADD COLUMN read_date DATE NOT NULL DEFAULT CURRENT_DATE; 