-- Mini ERP – Sales & Stock
-- Engine: InnoDB, charset: utf8mb4
-- Compatible: MySQL 5.7+ / MariaDB 10.3+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `warehouses` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `code`       VARCHAR(20)  NOT NULL,
    `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_warehouse_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `users` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username`      VARCHAR(80)  NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role`          ENUM('admin','user_warehouse') NOT NULL,
    `warehouse_id`  INT UNSIGNED NULL DEFAULT NULL,
    `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_username` (`username`),
    CONSTRAINT `fk_users_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `categories` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `products` (
    `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `code`           VARCHAR(50)     NOT NULL,
    `name`           VARCHAR(200)    NOT NULL,
    `category_id`    INT UNSIGNED    NOT NULL,
    `price`          DECIMAL(12,2)   NOT NULL,
    `alert_quantity` INT             NOT NULL DEFAULT 0,
    `is_active`      TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_product_code` (`code`),
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `stock` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`   INT UNSIGNED NOT NULL,
    `warehouse_id` INT UNSIGNED NOT NULL,
    `quantity`     INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_stock_product_warehouse` (`product_id`, `warehouse_id`),
    CONSTRAINT `fk_stock_product`   FOREIGN KEY (`product_id`)   REFERENCES `products`   (`id`),
    CONSTRAINT `fk_stock_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `customers` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(150) NOT NULL,
    `phone`      VARCHAR(30)  NULL DEFAULT NULL,
    `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `invoices` (
    `id`               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `invoice_no`       VARCHAR(30)   NOT NULL,
    `customer_id`      INT UNSIGNED  NOT NULL,
    `warehouse_id`     INT UNSIGNED  NOT NULL,
    `user_id`          INT UNSIGNED  NOT NULL,
    `subtotal`         DECIMAL(12,2) NOT NULL,
    `discount_percent` DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
    `discount_amount`  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total`            DECIMAL(12,2) NOT NULL,
    `created_at`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_invoice_no` (`invoice_no`),
    KEY `idx_invoices_created_at` (`created_at`),
    CONSTRAINT `fk_invoices_customer`  FOREIGN KEY (`customer_id`)  REFERENCES `customers`  (`id`),
    CONSTRAINT `fk_invoices_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
    CONSTRAINT `fk_invoices_user`      FOREIGN KEY (`user_id`)      REFERENCES `users`      (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `invoice_lines` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `invoice_id` INT UNSIGNED  NOT NULL,
    `product_id` INT UNSIGNED  NOT NULL,
    `qty`        INT           NOT NULL,
    `unit_price` DECIMAL(12,2) NOT NULL,
    `line_total` DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_invoice_lines_invoice_id` (`invoice_id`),
    CONSTRAINT `fk_lines_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
    CONSTRAINT `fk_lines_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;