-- Seed data for Mini ERP
-- Run after schema.sql
-- Default credentials:
--   admin       / admin123
--   warehouse1  / pass123
--   warehouse2  / pass123

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Warehouses
INSERT INTO `warehouses` (`id`, `name`, `code`) VALUES
(1, 'Main Warehouse',  'WH-MAIN'),
(2, 'North Warehouse', 'WH-NORTH');

-- Users
-- Hashes generated with password_hash($plain, PASSWORD_BCRYPT)
INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `warehouse_id`) VALUES
(1, 'admin',      '$2y$10$BmMsUC7KqhMSO2MQrNfvCuYmVCsqSqnMyxuaxyRY8FMYep3FG3S12', 'admin',          NULL),
(2, 'warehouse1', '$2y$10$tckonGdzs6AYnV87kEKx7OjjXweKiNu/QFjA.fZvzIkcxuSGcSorC', 'user_warehouse',  1),
(3, 'warehouse2', '$2y$10$tckonGdzs6AYnV87kEKx7OjjXweKiNu/QFjA.fZvzIkcxuSGcSorC', 'user_warehouse',  2);

-- Categories
INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Electronics'),
(2, 'Stationery'),
(3, 'Consumables');

-- Products (12 items)
INSERT INTO `products` (`id`, `code`, `name`, `category_id`, `price`, `alert_quantity`) VALUES
(1,  'ELEC-001', 'Laptop 15"',          1,  1250.00, 3),
(2,  'ELEC-002', 'Wireless Mouse',      1,    25.00, 10),
(3,  'ELEC-003', 'USB-C Hub 7-Port',    1,    45.00, 5),
(4,  'ELEC-004', 'Mechanical Keyboard', 1,    85.00, 5),
(5,  'ELEC-005', 'Monitor 24"',         1,   320.00, 3),
(6,  'STAT-001', 'A4 Paper (ream)',      2,     6.50, 20),
(7,  'STAT-002', 'Ballpoint Pens (box)', 2,    4.00, 15),
(8,  'STAT-003', 'Stapler',             2,    12.00, 8),
(9,  'STAT-004', 'Sticky Notes Pack',   2,     3.50, 10),
(10, 'CONS-001', 'Printer Ink Black',   3,    18.00, 5),
(11, 'CONS-002', 'Printer Ink Color',   3,    22.00, 5),
(12, 'CONS-003', 'Cleaning Wipes Pack', 3,     8.00, 10);

-- Stock — deliberately mixed: some rows below alert_quantity to demo the low-stock report
-- Main Warehouse (WH-MAIN)
INSERT INTO `stock` (`product_id`, `warehouse_id`, `quantity`) VALUES
(1,  1, 8),
(2,  1, 50),
(3,  1, 12),
(4,  1, 2),   -- below alert (5)
(5,  1, 5),
(6,  1, 100),
(7,  1, 60),
(8,  1, 4),   -- below alert (8)
(9,  1, 30),
(10, 1, 3),   -- below alert (5)
(11, 1, 7),
(12, 1, 25);

-- North Warehouse (WH-NORTH)
INSERT INTO `stock` (`product_id`, `warehouse_id`, `quantity`) VALUES
(1,  2, 3),
(2,  2, 20),
(3,  2, 1),   -- below alert (5)
(4,  2, 10),
(5,  2, 2),   -- below alert (3)
(6,  2, 40),
(7,  2, 8),
(10, 2, 0),   -- below alert (5)
(11, 2, 2),   -- below alert (5)
(12, 2, 15);

-- Customers
INSERT INTO `customers` (`id`, `name`, `phone`) VALUES
(1, 'Acme Corp',      '+1-555-0100'),
(2, 'Beta Trading',   '+1-555-0101'),
(3, 'Gamma Supplies', '+1-555-0102');

SET FOREIGN_KEY_CHECKS = 1;