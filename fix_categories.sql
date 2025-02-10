SET FOREIGN_KEY_CHECKS=0;

-- Drop the index on category_id from products table
ALTER TABLE products DROP INDEX category_id;

-- Drop the categories table
DROP TABLE IF EXISTS categories;

-- Create the categories table with the correct structure
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    FOREIGN KEY (updated_by) REFERENCES users(user_id)
);

-- Add the foreign key constraint back to products table
ALTER TABLE products
ADD CONSTRAINT fk_product_category
FOREIGN KEY (category_id) REFERENCES categories(category_id);

SET FOREIGN_KEY_CHECKS=1; 