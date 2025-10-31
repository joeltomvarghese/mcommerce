-- Database: mcommerce_app
-- Create the users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create the products table for M-Commerce listing
CREATE TABLE IF NOT EXISTS products (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT(11) NOT NULL DEFAULT 0,
    image_url VARCHAR(255)
);

-- **************************************************
-- ** NEW CART TABLE FOR M-COMMERCE FUNCTIONALITY **
-- **************************************************
CREATE TABLE IF NOT EXISTS cart (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) UNSIGNED NOT NULL,
    product_id INT(11) UNSIGNED NOT NULL,
    quantity INT(5) NOT NULL DEFAULT 1,
    
    -- Foreign keys to link cart items to users and products
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    
    -- Prevents adding the same product multiple times by simply updating quantity
    UNIQUE KEY user_product_unique (user_id, product_id)
);

-- Insert sample data into the products table (Run only if table is empty)
INSERT INTO products (name, description, price, stock, image_url) VALUES
('Smartphone X', 'The latest model with a 5G chip and amazing camera.', 699.99, 50, 'https://placehold.co/400x300/1D4ED8/ffffff?text=Phone+X'),
('Wireless Earbuds', 'Noise-cancelling, 24-hour battery life, ergonomic fit.', 129.50, 120, 'https://placehold.co/400x300/059669/ffffff?text=Earbuds'),
('Smart Watch Pro', 'Fitness tracking, heart rate monitor, and notifications.', 249.00, 75, 'https://placehold.co/400x300/991B1B/ffffff?text=Watch+Pro')
ON DUPLICATE KEY UPDATE name=name; -- Prevents re-insertion on subsequent runs
