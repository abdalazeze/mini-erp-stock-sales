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
(3, 'Consumables'),
(4, 'Furniture');

-- Products (30 items — enough to show 2 pages of pagination at 20/page)
INSERT INTO `products` (`id`, `code`, `name`, `category_id`, `price`, `alert_quantity`) VALUES
(1,  'ELEC-001', 'Laptop 15"',              1,  1250.00,  3),
(2,  'ELEC-002', 'Wireless Mouse',          1,    25.00, 10),
(3,  'ELEC-003', 'USB-C Hub 7-Port',        1,    45.00,  5),
(4,  'ELEC-004', 'Mechanical Keyboard',     1,    85.00,  5),
(5,  'ELEC-005', 'Monitor 24"',             1,   320.00,  3),
(6,  'ELEC-006', 'Webcam 1080p',            1,    60.00,  5),
(7,  'ELEC-007', 'Wireless Headset',        1,   110.00,  5),
(8,  'ELEC-008', 'External SSD 1TB',        1,    95.00,  4),
(9,  'ELEC-009', 'Laptop Stand',            1,    35.00,  6),
(10, 'ELEC-010', 'HDMI Cable 2m',           1,    12.00, 10),
(11, 'STAT-001', 'A4 Paper (ream)',          2,     6.50, 20),
(12, 'STAT-002', 'Ballpoint Pens (box)',     2,     4.00, 15),
(13, 'STAT-003', 'Stapler',                 2,    12.00,  8),
(14, 'STAT-004', 'Sticky Notes Pack',       2,     3.50, 10),
(15, 'STAT-005', 'Whiteboard Markers (set)',2,     8.00, 10),
(16, 'STAT-006', 'Scissors',               2,     5.00,  8),
(17, 'STAT-007', 'Folder A4 (pack of 10)', 2,     9.00, 10),
(18, 'STAT-008', 'Correction Tape',        2,     2.50, 12),
(19, 'CONS-001', 'Printer Ink Black',       3,    18.00,  5),
(20, 'CONS-002', 'Printer Ink Color',       3,    22.00,  5),
(21, 'CONS-003', 'Cleaning Wipes Pack',     3,     8.00, 10),
(22, 'CONS-004', 'Hand Sanitiser 500ml',    3,     5.50, 15),
(23, 'CONS-005', 'Coffee (500g)',           3,    14.00,  8),
(24, 'CONS-006', 'Paper Towels (roll)',     3,     3.00, 20),
(25, 'CONS-007', 'Dish Soap 1L',           3,     4.00, 12),
(26, 'FURN-001', 'Office Chair',           4,   220.00,  2),
(27, 'FURN-002', 'Standing Desk',          4,   450.00,  2),
(28, 'FURN-003', 'Bookshelf 5-Tier',       4,   130.00,  2),
(29, 'FURN-004', 'Monitor Arm',            4,    75.00,  3),
(30, 'FURN-005', 'Cable Management Tray',  4,    20.00,  5);

-- Stock — deliberately mixed: some rows below alert_quantity to demo the low-stock report
-- Main Warehouse (WH-MAIN)
INSERT INTO `stock` (`product_id`, `warehouse_id`, `quantity`) VALUES
(1,  1, 8),
(2,  1, 50),
(3,  1, 12),
(4,  1, 2),   -- below alert (5)
(5,  1, 5),
(6,  1, 18),
(7,  1, 10),
(8,  1, 6),
(9,  1, 4),   -- below alert (6)
(10, 1, 30),
(11, 1, 100),
(12, 1, 60),
(13, 1, 4),   -- below alert (8)
(14, 1, 30),
(15, 1, 20),
(16, 1, 15),
(17, 1, 25),
(18, 1, 40),
(19, 1, 3),   -- below alert (5)
(20, 1, 7),
(21, 1, 25),
(22, 1, 30),
(23, 1, 5),   -- below alert (8)
(24, 1, 50),
(25, 1, 20),
(26, 1, 4),
(27, 1, 1),   -- below alert (2)
(28, 1, 3),
(29, 1, 2),   -- below alert (3)
(30, 1, 10);

-- North Warehouse (WH-NORTH)
INSERT INTO `stock` (`product_id`, `warehouse_id`, `quantity`) VALUES
(1,  2, 3),
(2,  2, 20),
(3,  2, 1),   -- below alert (5)
(4,  2, 10),
(5,  2, 2),   -- below alert (3)
(6,  2, 5),
(7,  2, 3),   -- below alert (5)
(8,  2, 4),
(11, 2, 40),
(12, 2, 8),
(13, 2, 12),
(14, 2, 15),
(19, 2, 0),   -- below alert (5)
(20, 2, 2),   -- below alert (5)
(21, 2, 15),
(26, 2, 1),   -- below alert (2)
(27, 2, 2),
(28, 2, 5),
(29, 2, 1),   -- below alert (3)
(30, 2, 8);

-- Customers
INSERT INTO `customers` (`id`, `name`, `phone`) VALUES
(1, 'Acme Corp',      '+1-555-0100'),
(2, 'Beta Trading',   '+1-555-0101'),
(3, 'Gamma Supplies', '+1-555-0102');

SET FOREIGN_KEY_CHECKS = 1;