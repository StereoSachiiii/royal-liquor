-- Royal Liquor Seed Data
-- Default Categories
INSERT INTO categories (name, slug, description, image_url) VALUES
('Whiskey', 'whiskey', 'Premium aged spirits from around the world.', 'whiskey.jpg'),
('Vodka', 'vodka', 'Pure, distilled spirits for cocktails and neat drinking.', 'vodka.jpg'),
('Rum', 'rum', 'Tropical and dark aged rums.', 'rum.jpg'),
('Wine', 'wine', 'Fine red, white and sparkling wines.', 'wine.jpg'),
('Gin', 'gin', 'Botanical-infused spirits.', 'placeholder-spirit.png')
ON CONFLICT (slug) DO NOTHING;

-- Default Suppliers
INSERT INTO suppliers (name, email) VALUES
('Global Spirits Distributing', 'orders@globalspirits.com'),
('Fine Wines Import Co.', 'contact@finewines.com')
ON CONFLICT (name) DO NOTHING;

-- Default Warehouses
INSERT INTO warehouses (name, address) VALUES
('Colombo Central', '123 Galle Road, Colombo 03'),
('Kandy Distribution', '45 Main Street, Kandy')
ON CONFLICT (name) DO NOTHING;

-- Default Products
INSERT INTO products (name, slug, description, price_cents, image_url, category_id, supplier_id) VALUES
('Johnnie Walker Black Label', 'johnnie-walker-black', 'Iconic blended scotch whiskey.', 850000, 'johnnie-walker-black.jpg', 1, 1),
('Absolut Vodka', 'absolut-vodka', 'Pure Swedish vodka.', 450000, 'vodka.jpg', 2, 1),
('Bacardi Superior White Rum', 'bacardi-rum', 'Classic mixing rum.', 380000, 'rum.jpg', 3, 1),
('Casillero del Diablo Cabernet', 'casillero-cabernet', 'Rich Chilean red wine.', 320000, 'wine.jpg', 4, 2)
ON CONFLICT (slug) DO NOTHING;

-- Initial Stock Levels
INSERT INTO stock (product_id, warehouse_id, quantity, reserved) VALUES
(1, 1, 100, 0),
(2, 1, 150, 0),
(3, 1, 200, 0),
(4, 2, 50, 0)
ON CONFLICT (product_id, warehouse_id) DO NOTHING;

-- Default Admin User (Password: Admin123!)
INSERT INTO users (name, email, password_hash, is_admin) VALUES
('System Admin', 'admin@royal-liquor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE)
ON CONFLICT (email) DO NOTHING;
