CREATE DATABASE shop
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_uca1400_ai_ci;

USE shop;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL,
    description TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB;

CREATE USER 'shop_app'@'127.0.0.1'
    IDENTIFIED BY 'secret123';

GRANT SELECT, INSERT, UPDATE, DELETE
    ON shop.*
    TO 'shop_app'@'127.0.0.1';

INSERT INTO products (name, description)
VALUES
    ('Product One', 'Product one description'),
    ('Product Two', NULL),
    ('Product Three', '<b>HTML</b> tags in the Product Three description');