CREATE DATABASE IF NOT EXISTS tunehub;
USE tunehub;

CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('Customer', 'Admin') DEFAULT 'Customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE Products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    description TEXT,
    brand VARCHAR(100),
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    skill_level ENUM('Beginner', 'Intermediate', 'Advanced') NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES Categories(category_id) ON DELETE RESTRICT,
    FULLTEXT(product_name, description, brand)
);

CREATE TABLE Tutorials (
    tutorial_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    video_url VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (product_id) REFERENCES Products(product_id) ON DELETE CASCADE
);

CREATE TABLE Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE RESTRICT
);

CREATE TABLE Order_Items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Products(product_id) ON DELETE RESTRICT
);

-- Mock Data (10 Instruments + Categories)
INSERT INTO Categories (category_name) VALUES 
('Guitars'), ('Keyboards'), ('Drums'), ('Strings'), ('Wind');

INSERT INTO Products (product_name, category_id, description, brand, price, stock_quantity, skill_level, image_url) VALUES
('Fender Stratocaster', 1, 'Classic electric guitar with versatile tone.', 'Fender', 799.99, 15, 'Intermediate', 'fender_strat.jpg'),
('Yamaha PAC112V', 1, 'Great beginner electric guitar with solid value.', 'Yamaha', 299.99, 30, 'Beginner', 'yamaha_pac112v.jpg'),
('Gibson Les Paul Standard', 1, 'Iconic rock guitar with rich, thick tone.', 'Gibson', 2499.00, 5, 'Advanced', 'gibson_les_paul.jpg'),
('Roland FP-30X', 2, 'Compact digital piano with authentic feel.', 'Roland', 699.99, 20, 'Intermediate', 'roland_fp30x.jpg'),
('Yamaha P-45', 2, 'Affordable digital piano for beginners.', 'Yamaha', 549.99, 25, 'Beginner', 'yamaha_p45.jpg'),
('Alesis Nitro Mesh Kit', 3, 'Electronic drum kit with quiet mesh heads.', 'Alesis', 379.00, 10, 'Beginner', 'alesis_nitro.jpg'),
('Pearl Export EXX', 3, 'Reliable acoustic drum set.', 'Pearl', 899.00, 8, 'Intermediate', 'pearl_export.jpg'),
('Cecilio CVN-300', 4, 'Solid wood violin with bow and case.', 'Cecilio', 159.99, 40, 'Beginner', 'cecilio_cvn300.jpg'),
('Yamaha V5SC', 4, 'High-quality student violin.', 'Yamaha', 599.00, 12, 'Intermediate', 'yamaha_v5sc.jpg'),
('Yamaha YAS-280', 5, 'Excellent student alto saxophone.', 'Yamaha', 1299.00, 6, 'Beginner', 'yamaha_yas280.jpg');

-- Mock Users (Customer & Admin)
-- Admin pass: 'admin123', Customer pass: 'customer123' (will be hashed in PHP, using pre-hashed value for example)
-- Let's just create one admin for dashboard. Password is 'password' hashed with cost 12.
INSERT INTO Users (username, email, password_hash, user_type) VALUES
('admin', 'admin@tunehub.local', '$2y$12$Kk0r7aC10O/eXGj9mHhT.eYJ8A2pZ0f76.LhXzJd19vQ.m6E5O0U.', 'Admin'),
('johndoe', 'customer@tunehub.local', '$2y$12$Kk0r7aC10O/eXGj9mHhT.eYJ8A2pZ0f76.LhXzJd19vQ.m6E5O0U.', 'Customer');

-- Mock Tutorials
INSERT INTO Tutorials (product_id, title, video_url) VALUES
(1, 'Fender Stratocaster Setup Guide', 'https://www.youtube.com/embed/O_x_hN1gG14'),
(2, 'Beginner Guitar Lesson 1', 'https://www.youtube.com/embed/BBz-Jyr23M4'),
(4, 'Getting Started with Roland FP-30X', 'https://www.youtube.com/embed/xSxjzG4w0u4');

-- Advanced Modules (Reviews, Wishlist, Promotions)
CREATE TABLE IF NOT EXISTS Reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK(rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES Products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    UNIQUE(product_id, user_id)
);

CREATE TABLE IF NOT EXISTS Wishlist (
    wishlist_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Products(product_id) ON DELETE CASCADE,
    UNIQUE(user_id, product_id)
);

CREATE TABLE IF NOT EXISTS Promotions (
    promo_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_percent DECIMAL(5,2) NOT NULL CHECK(discount_percent > 0 AND discount_percent <= 100),
    valid_until DATETIME DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin Panel Expansions (Queries, Feedbacks)
CREATE TABLE IF NOT EXISTS Queries (
    query_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('Pending', 'Reviewed', 'Resolved') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS Feedbacks (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('Unread', 'Read') DEFAULT 'Unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

