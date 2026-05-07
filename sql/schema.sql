-- ============================================================
-- MISAKI HANDCRAFTED — MariaDB / MySQL schema (3NF)
-- Run inside phpMyAdmin (XAMPP) or `mysql -u root < schema.sql`
-- ============================================================
CREATE DATABASE IF NOT EXISTS misaki
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE misaki;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS review;
DROP TABLE IF EXISTS order_item_addon;
DROP TABLE IF EXISTS order_item;
DROP TABLE IF EXISTS `order`;
DROP TABLE IF EXISTS addon;
DROP TABLE IF EXISTS product;
DROP TABLE IF EXISTS product_type;
DROP TABLE IF EXISTS user;
DROP TABLE IF EXISTS admin_user;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------- USERS ----------
CREATE TABLE user (
  user_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email          VARCHAR(190) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  full_name      VARCHAR(120) NOT NULL,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE admin_user (
  admin_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username       VARCHAR(60) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- PRODUCT CATALOG ----------
CREATE TABLE product_type (
  type_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name      VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE product (
  product_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug          VARCHAR(120) NOT NULL UNIQUE,
  name          VARCHAR(120) NOT NULL,
  jp_name       VARCHAR(60)  NOT NULL DEFAULT '',
  type_id       INT UNSIGNED NOT NULL,
  price         DECIMAL(10,2) NOT NULL,
  image         VARCHAR(255) NOT NULL,
  badge         VARCHAR(40)  NULL,
  description   TEXT NOT NULL,
  sales         INT UNSIGNED NOT NULL DEFAULT 0,
  is_visible    TINYINT(1) NOT NULL DEFAULT 1,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_product_type FOREIGN KEY (type_id) REFERENCES product_type(type_id)
) ENGINE=InnoDB;

CREATE TABLE addon (
  addon_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(80) NOT NULL UNIQUE,
  price       DECIMAL(10,2) NOT NULL,
  is_active   TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ---------- ORDERS ----------
CREATE TABLE `order` (
  order_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id      INT UNSIGNED NOT NULL,
  status       ENUM('pending','paid','fulfilled','cancelled') NOT NULL DEFAULT 'paid',
  total        DECIMAL(10,2) NOT NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES user(user_id)
) ENGINE=InnoDB;

CREATE TABLE order_item (
  order_item_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id      INT UNSIGNED NOT NULL,
  product_id    INT UNSIGNED NOT NULL,
  qty           INT UNSIGNED NOT NULL,
  unit_price    DECIMAL(10,2) NOT NULL,    -- product price snapshot
  line_total    DECIMAL(10,2) NOT NULL,    -- (unit_price + sum addons) * qty
  CONSTRAINT fk_oi_order   FOREIGN KEY (order_id)   REFERENCES `order`(order_id) ON DELETE CASCADE,
  CONSTRAINT fk_oi_product FOREIGN KEY (product_id) REFERENCES product(product_id)
) ENGINE=InnoDB;

CREATE TABLE order_item_addon (
  order_item_id INT UNSIGNED NOT NULL,
  addon_id      INT UNSIGNED NOT NULL,
  unit_price    DECIMAL(10,2) NOT NULL,    -- addon price snapshot
  PRIMARY KEY (order_item_id, addon_id),
  CONSTRAINT fk_oia_oi    FOREIGN KEY (order_item_id) REFERENCES order_item(order_item_id) ON DELETE CASCADE,
  CONSTRAINT fk_oia_addon FOREIGN KEY (addon_id)      REFERENCES addon(addon_id)
) ENGINE=InnoDB;

-- ---------- REVIEWS ----------
CREATE TABLE review (
  review_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id   INT UNSIGNED NOT NULL,
  user_id      INT UNSIGNED NOT NULL,
  order_id     INT UNSIGNED NOT NULL,
  rating       TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
  body         TEXT NOT NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_review_per_order_product (order_id, product_id),
  CONSTRAINT fk_review_product FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE,
  CONSTRAINT fk_review_user    FOREIGN KEY (user_id)    REFERENCES user(user_id),
  CONSTRAINT fk_review_order   FOREIGN KEY (order_id)   REFERENCES `order`(order_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================
INSERT INTO product_type (name) VALUES
  ('Bouquets'),('Arrangements'),('Dried'),('Seasonal');

INSERT INTO product (slug,name,jp_name,type_id,price,image,badge,description,sales) VALUES
  ('lorem-blush','Lorem Blush','桃の夢',1,48,'images/product-1.jpg','Bestseller','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Hand-tied with garden roses and seasonal foliage, wrapped in unbleached kraft.',320),
  ('lorem-aurora','Lorem Aurora','白の静寂',2,62,'images/product-2.jpg','New','Lorem ipsum dolor sit amet. White peonies and baby''s breath in a hand-thrown ceramic vessel, made for quiet rooms.',120),
  ('lorem-amber','Lorem Amber','枯れ草',3,36,'images/product-3.jpg','Limited','Lorem ipsum. Pampas and dried lavender bound with twine — lasts a full season, no water needed.',210),
  ('lorem-solis','Lorem Solis','陽の花',4,54,'images/product-4.jpg',NULL,'Lorem ipsum dolor. Sunflowers and silver eucalyptus, wrapped in warm kraft for a summer table.',180),
  ('lorem-sakura','Lorem Sakura','桜枝',2,88,'images/product-5.jpg','Bestseller','Lorem ipsum. Cherry blossom branches arranged ikebana-style in a stoneware vase. Signature piece.',410),
  ('lorem-meadow','Lorem Meadow','野の花',1,42,'images/product-6.jpg','New','Lorem ipsum dolor sit amet. Wild chamomile, cosmos and ferns gathered loosely — feels like a walk through a meadow.',95);

INSERT INTO addon (name,price,is_active) VALUES
  ('Printed Photo',     5.00, 1),
  ('Acrylic Dedication',5.00, 1),
  ('Fairy Light',      20.00, 1),
  ('Letter',           25.00, 1);

-- default admin: admin / admin123  (change after first login!)
INSERT INTO admin_user (username, password_hash) VALUES
  ('admin', '$2y$10$e0NRZk9d8IPM2OG3O4FXR.qKp7VbQk8yQpm8LpQH2jM4o6sWnY4Ki');
-- hash above = password_hash('admin123', PASSWORD_BCRYPT)
