-- Up: Seed 50 luxury demo products with deterministic local transparent product images
-- Images are stored in uploads/ as lux_20260531_*.webp.

SET @fashion_id = (SELECT category_id FROM categories WHERE slug = 'fashion' LIMIT 1);
SET @electronics_id = (SELECT category_id FROM categories WHERE slug = 'electronics' LIMIT 1);
SET @home_id = (SELECT category_id FROM categories WHERE slug = 'home' LIMIT 1);
SET @beauty_id = (SELECT category_id FROM categories WHERE slug = 'beauty' LIMIT 1);

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Gucci Jackie Small Shoulder Bag', @fashion_id, 'A compact luxury shoulder bag demo listing with a sculptural profile, polished hardware, and a clean three-image gallery for premium merchandising. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 1690.00, 'lux_20260531_fashion_gucci-jackie-small-shoulder-bag_01.webp', 12, '["lux_20260531_fashion_gucci-jackie-small-shoulder-bag_02.webp", "lux_20260531_fashion_gucci-jackie-small-shoulder-bag_03.webp"]', NULL
WHERE @fashion_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Gucci Jackie Small Shoulder Bag' AND image = 'lux_20260531_fashion_gucci-jackie-small-shoulder-bag_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Prada Re-Nylon Backpack', @fashion_id, 'A refined backpack demo listing inspired by technical nylon, streamlined pockets, and a travel-ready luxury silhouette. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 2250.00, 'lux_20260531_fashion_prada-re-nylon-backpack_01.webp', 12, '["lux_20260531_fashion_prada-re-nylon-backpack_02.webp", "lux_20260531_fashion_prada-re-nylon-backpack_03.webp"]', NULL
WHERE @fashion_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Prada Re-Nylon Backpack' AND image = 'lux_20260531_fashion_prada-re-nylon-backpack_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Saint Laurent Sac de Jour Small', @fashion_id, 'A structured top-handle bag demo listing with tailored proportions, smooth leather styling, and understated executive polish. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 3400.00, 'lux_20260531_fashion_saint-laurent-sac-de-jour-small_01.webp', 12, '["lux_20260531_fashion_saint-laurent-sac-de-jour-small_02.webp", "lux_20260531_fashion_saint-laurent-sac-de-jour-small_03.webp"]', NULL
WHERE @fashion_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Saint Laurent Sac de Jour Small' AND image = 'lux_20260531_fashion_saint-laurent-sac-de-jour-small_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Louis Vuitton Neverfull MM', @fashion_id, 'A roomy tote demo listing for premium daily carry, styled with an open-top profile and polished luxury presentation. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 2030.00, 'lux_20260531_fashion_louis-vuitton-neverfull-mm_01.webp', 12, '["lux_20260531_fashion_louis-vuitton-neverfull-mm_02.webp", "lux_20260531_fashion_louis-vuitton-neverfull-mm_03.webp"]', NULL
WHERE @fashion_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Louis Vuitton Neverfull MM' AND image = 'lux_20260531_fashion_louis-vuitton-neverfull-mm_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Hermes Oran Sandal', @fashion_id, 'A minimalist sandal demo listing with a clean slip-on silhouette, refined finish, and resort-ready luxury styling. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 760.00, 'lux_20260531_fashion_hermes-oran-sandal_01.webp', 12, '["lux_20260531_fashion_hermes-oran-sandal_02.webp", "lux_20260531_fashion_hermes-oran-sandal_03.webp"]', NULL
WHERE @fashion_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Hermes Oran Sandal' AND image = 'lux_20260531_fashion_hermes-oran-sandal_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Chanel Classic Flap Bag', @fashion_id, 'A timeless flap bag demo listing with quilted-bag positioning, chain-strap elegance, and collectible luxury appeal. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 10800.00, 'lux_20260531_fashion_chanel-classic-flap-bag_01.webp', 12, '["lux_20260531_fashion_chanel-classic-flap-bag_02.webp", "lux_20260531_fashion_chanel-classic-flap-bag_03.webp"]', NULL
WHERE @fashion_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Chanel Classic Flap Bag' AND image = 'lux_20260531_fashion_chanel-classic-flap-bag_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Burberry Kensington Trench Coat', @fashion_id, 'A heritage trench coat demo listing with tailored outerwear styling, classic check-inspired merchandising, and all-season polish. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 2790.00, 'lux_20260531_fashion_burberry-kensington-trench-coat_01.webp', 12, '["lux_20260531_fashion_burberry-kensington-trench-coat_02.webp", "lux_20260531_fashion_burberry-kensington-trench-coat_03.webp"]', NULL
WHERE @fashion_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Burberry Kensington Trench Coat' AND image = 'lux_20260531_fashion_burberry-kensington-trench-coat_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Bottega Veneta Andiamo Bag', @fashion_id, 'A soft-structure handbag demo listing with woven-luxury positioning, versatile carry styling, and gallery-ready transparent assets. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 4500.00, 'lux_20260531_fashion_bottega-veneta-andiamo-bag_01.webp', 12, '["lux_20260531_fashion_bottega-veneta-andiamo-bag_02.webp", "lux_20260531_fashion_bottega-veneta-andiamo-bag_03.webp"]', NULL
WHERE @fashion_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Bottega Veneta Andiamo Bag' AND image = 'lux_20260531_fashion_bottega-veneta-andiamo-bag_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Valentino Garavani Rockstud Pump', @fashion_id, 'A pointed pump demo listing with dress-shoe styling, polished accents, and an eveningwear luxury presentation. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 1190.00, 'lux_20260531_fashion_valentino-garavani-rockstud-pump_01.webp', 12, '["lux_20260531_fashion_valentino-garavani-rockstud-pump_02.webp", "lux_20260531_fashion_valentino-garavani-rockstud-pump_03.webp"]', NULL
WHERE @fashion_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Valentino Garavani Rockstud Pump' AND image = 'lux_20260531_fashion_valentino-garavani-rockstud-pump_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Balenciaga Triple S Sneaker', @fashion_id, 'A statement sneaker demo listing with layered athletic styling, elevated streetwear energy, and premium catalog imagery. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 1150.00, 'lux_20260531_fashion_balenciaga-triple-s-sneaker_01.webp', 12, '["lux_20260531_fashion_balenciaga-triple-s-sneaker_02.webp", "lux_20260531_fashion_balenciaga-triple-s-sneaker_03.webp"]', NULL
WHERE @fashion_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Balenciaga Triple S Sneaker' AND image = 'lux_20260531_fashion_balenciaga-triple-s-sneaker_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Cartier Tank Must Watch', @fashion_id, 'A rectangular dress watch demo listing with refined leather-strap styling, elegant dial proportions, and gift-ready positioning. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 3300.00, 'lux_20260531_fashion_cartier-tank-must-watch_01.webp', 12, '["lux_20260531_fashion_cartier-tank-must-watch_02.webp", "lux_20260531_fashion_cartier-tank-must-watch_03.webp"]', NULL
WHERE @fashion_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Cartier Tank Must Watch' AND image = 'lux_20260531_fashion_cartier-tank-must-watch_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Rolex Datejust 36', @fashion_id, 'A classic automatic watch demo listing with metal-bracelet styling, polished case presentation, and luxury timepiece appeal. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 7950.00, 'lux_20260531_fashion_rolex-datejust-36_01.webp', 12, '["lux_20260531_fashion_rolex-datejust-36_02.webp", "lux_20260531_fashion_rolex-datejust-36_03.webp"]', NULL
WHERE @fashion_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Rolex Datejust 36' AND image = 'lux_20260531_fashion_rolex-datejust-36_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Montblanc Meisterstuck Classique', @fashion_id, 'A premium writing instrument demo listing with black-and-metal styling, executive desk appeal, and refined gift positioning. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 520.00, 'lux_20260531_fashion_montblanc-meisterstuck-classique_01.webp', 12, '["lux_20260531_fashion_montblanc-meisterstuck-classique_02.webp", "lux_20260531_fashion_montblanc-meisterstuck-classique_03.webp"]', NULL
WHERE @fashion_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Montblanc Meisterstuck Classique' AND image = 'lux_20260531_fashion_montblanc-meisterstuck-classique_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Apple Vision Pro', @electronics_id, 'A premium spatial-computing headset demo listing with immersive display positioning, sleek wearable styling, and transparent catalog images. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 3499.00, 'lux_20260531_electronics_apple-vision-pro_01.webp', 12, '["lux_20260531_electronics_apple-vision-pro_02.webp", "lux_20260531_electronics_apple-vision-pro_03.webp"]', NULL
WHERE @electronics_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Apple Vision Pro' AND image = 'lux_20260531_electronics_apple-vision-pro_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Apple MacBook Pro 16-inch', @electronics_id, 'A professional laptop demo listing with large-screen performance positioning, slim aluminum styling, and three clean product angles. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 2499.00, 'lux_20260531_electronics_apple-macbook-pro-16-inch_01.webp', 12, '["lux_20260531_electronics_apple-macbook-pro-16-inch_02.webp", "lux_20260531_electronics_apple-macbook-pro-16-inch_03.webp"]', NULL
WHERE @electronics_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Apple MacBook Pro 16-inch' AND image = 'lux_20260531_electronics_apple-macbook-pro-16-inch_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Apple AirPods Max', @electronics_id, 'A luxury over-ear headphone demo listing with aluminum styling, premium audio positioning, and a polished gallery presentation. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 549.00, 'lux_20260531_electronics_apple-airpods-max_01.webp', 12, '["lux_20260531_electronics_apple-airpods-max_02.webp", "lux_20260531_electronics_apple-airpods-max_03.webp"]', NULL
WHERE @electronics_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Apple AirPods Max' AND image = 'lux_20260531_electronics_apple-airpods-max_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Bang & Olufsen Beoplay H95', @electronics_id, 'A premium wireless headphone demo listing with travel-audio positioning, refined materials, and clean cutout imagery. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 999.00, 'lux_20260531_electronics_bang-and-olufsen-beoplay-h95_01.webp', 12, '["lux_20260531_electronics_bang-and-olufsen-beoplay-h95_02.webp", "lux_20260531_electronics_bang-and-olufsen-beoplay-h95_03.webp"]', NULL
WHERE @electronics_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Bang & Olufsen Beoplay H95' AND image = 'lux_20260531_electronics_bang-and-olufsen-beoplay-h95_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Bang & Olufsen Beosound A5', @electronics_id, 'A portable speaker demo listing with room-filling audio positioning, sculptural styling, and local transparent product assets. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 1099.00, 'lux_20260531_electronics_bang-and-olufsen-beosound-a5_01.webp', 12, '["lux_20260531_electronics_bang-and-olufsen-beosound-a5_02.webp", "lux_20260531_electronics_bang-and-olufsen-beosound-a5_03.webp"]', NULL
WHERE @electronics_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Bang & Olufsen Beosound A5' AND image = 'lux_20260531_electronics_bang-and-olufsen-beosound-a5_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Leica Q3', @electronics_id, 'A luxury compact camera demo listing with creator-focused positioning, precision optics styling, and high-end catalog presentation. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 5995.00, 'lux_20260531_electronics_leica-q3_01.webp', 12, '["lux_20260531_electronics_leica-q3_02.webp", "lux_20260531_electronics_leica-q3_03.webp"]', NULL
WHERE @electronics_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Leica Q3' AND image = 'lux_20260531_electronics_leica-q3_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Leica M11', @electronics_id, 'A rangefinder camera demo listing with heritage photography positioning, minimalist body styling, and premium cutout imagery. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 8995.00, 'lux_20260531_electronics_leica-m11_01.webp', 12, '["lux_20260531_electronics_leica-m11_02.webp", "lux_20260531_electronics_leica-m11_03.webp"]', NULL
WHERE @electronics_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Leica M11' AND image = 'lux_20260531_electronics_leica-m11_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Hasselblad X2D 100C', @electronics_id, 'A medium-format camera demo listing with studio-grade positioning, refined industrial design, and three transparent gallery views. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 8199.00, 'lux_20260531_electronics_hasselblad-x2d-100c_01.webp', 12, '["lux_20260531_electronics_hasselblad-x2d-100c_02.webp", "lux_20260531_electronics_hasselblad-x2d-100c_03.webp"]', NULL
WHERE @electronics_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Hasselblad X2D 100C' AND image = 'lux_20260531_electronics_hasselblad-x2d-100c_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Devialet Phantom I', @electronics_id, 'A sculptural wireless speaker demo listing with high-output audio positioning, compact luxury styling, and clean product visuals. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 3200.00, 'lux_20260531_electronics_devialet-phantom-i_01.webp', 12, '["lux_20260531_electronics_devialet-phantom-i_02.webp", "lux_20260531_electronics_devialet-phantom-i_03.webp"]', NULL
WHERE @electronics_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Devialet Phantom I' AND image = 'lux_20260531_electronics_devialet-phantom-i_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Sennheiser HE 1', @electronics_id, 'A reference headphone system demo listing with ultra-premium audio positioning and pristine transparent product merchandising. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 59000.00, 'lux_20260531_electronics_sennheiser-he-1_01.webp', 12, '["lux_20260531_electronics_sennheiser-he-1_02.webp", "lux_20260531_electronics_sennheiser-he-1_03.webp"]', NULL
WHERE @electronics_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Sennheiser HE 1' AND image = 'lux_20260531_electronics_sennheiser-he-1_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Dyson Supersonic Nural', @electronics_id, 'A high-end hair dryer demo listing with intelligent styling positioning, sleek body design, and clean gallery assets. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 499.99, 'lux_20260531_electronics_dyson-supersonic-nural_01.webp', 12, '["lux_20260531_electronics_dyson-supersonic-nural_02.webp", "lux_20260531_electronics_dyson-supersonic-nural_03.webp"]', NULL
WHERE @electronics_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Dyson Supersonic Nural' AND image = 'lux_20260531_electronics_dyson-supersonic-nural_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'LG Signature OLED M', @electronics_id, 'A flagship wireless OLED TV demo listing with cinematic display positioning, slim screen styling, and premium catalog imagery. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 4999.99, 'lux_20260531_electronics_lg-signature-oled-m_01.webp', 12, '["lux_20260531_electronics_lg-signature-oled-m_02.webp", "lux_20260531_electronics_lg-signature-oled-m_03.webp"]', NULL
WHERE @electronics_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'LG Signature OLED M' AND image = 'lux_20260531_electronics_lg-signature-oled-m_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Samsung The Premiere 9', @electronics_id, 'A premium ultra-short-throw projector demo listing with home-cinema positioning, compact design, and three transparent product views. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 5999.99, 'lux_20260531_electronics_samsung-the-premiere-9_01.webp', 12, '["lux_20260531_electronics_samsung-the-premiere-9_02.webp", "lux_20260531_electronics_samsung-the-premiere-9_03.webp"]', NULL
WHERE @electronics_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Samsung The Premiere 9' AND image = 'lux_20260531_electronics_samsung-the-premiere-9_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Baccarat Harcourt 1841 Glass', @home_id, 'A crystal glass demo listing with French tableware positioning, faceted styling, and elegant transparent product presentation. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 330.00, 'lux_20260531_home_baccarat-harcourt-1841-glass_01.webp', 12, '["lux_20260531_home_baccarat-harcourt-1841-glass_02.webp", "lux_20260531_home_baccarat-harcourt-1841-glass_03.webp"]', NULL
WHERE @home_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Baccarat Harcourt 1841 Glass' AND image = 'lux_20260531_home_baccarat-harcourt-1841-glass_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Lalique Mossi Vase', @home_id, 'A decorative vase demo listing with sculptural home styling, collectible luxury positioning, and clean gallery cutouts. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 1400.00, 'lux_20260531_home_lalique-mossi-vase_01.webp', 12, '["lux_20260531_home_lalique-mossi-vase_02.webp", "lux_20260531_home_lalique-mossi-vase_03.webp"]', NULL
WHERE @home_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Lalique Mossi Vase' AND image = 'lux_20260531_home_lalique-mossi-vase_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Frette Hotel Classic Sheet Set', @home_id, 'A premium bedding demo listing with hotel-linen positioning, tailored bedroom styling, and soft luxury merchandising. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 550.00, 'lux_20260531_home_frette-hotel-classic-sheet-set_01.webp', 12, '["lux_20260531_home_frette-hotel-classic-sheet-set_02.webp", "lux_20260531_home_frette-hotel-classic-sheet-set_03.webp"]', NULL
WHERE @home_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Frette Hotel Classic Sheet Set' AND image = 'lux_20260531_home_frette-hotel-classic-sheet-set_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Smeg Dolce & Gabbana Kettle', @home_id, 'A decorative electric kettle demo listing with statement kitchen styling, collector appeal, and local transparent images. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 650.00, 'lux_20260531_home_smeg-dolce-and-gabbana-kettle_01.webp', 12, '["lux_20260531_home_smeg-dolce-and-gabbana-kettle_02.webp", "lux_20260531_home_smeg-dolce-and-gabbana-kettle_03.webp"]', NULL
WHERE @home_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Smeg Dolce & Gabbana Kettle' AND image = 'lux_20260531_home_smeg-dolce-and-gabbana-kettle_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Alessi Plisse Electric Kettle', @home_id, 'A designer electric kettle demo listing with pleated-form positioning, compact kitchen styling, and clean product gallery assets. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 145.00, 'lux_20260531_home_alessi-plisse-electric-kettle_01.webp', 12, '["lux_20260531_home_alessi-plisse-electric-kettle_02.webp", "lux_20260531_home_alessi-plisse-electric-kettle_03.webp"]', NULL
WHERE @home_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Alessi Plisse Electric Kettle' AND image = 'lux_20260531_home_alessi-plisse-electric-kettle_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Georg Jensen Cobra Candleholder', @home_id, 'A sculptural candleholder demo listing with polished tabletop styling, modern Nordic positioning, and transparent gallery views. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 225.00, 'lux_20260531_home_georg-jensen-cobra-candleholder_01.webp', 12, '["lux_20260531_home_georg-jensen-cobra-candleholder_02.webp", "lux_20260531_home_georg-jensen-cobra-candleholder_03.webp"]', NULL
WHERE @home_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Georg Jensen Cobra Candleholder' AND image = 'lux_20260531_home_georg-jensen-cobra-candleholder_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Vitra Eames Lounge Chair', @home_id, 'A design-classic lounge chair demo listing with mid-century positioning, statement seating style, and premium catalog presentation. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 8995.00, 'lux_20260531_home_vitra-eames-lounge-chair_01.webp', 12, '["lux_20260531_home_vitra-eames-lounge-chair_02.webp", "lux_20260531_home_vitra-eames-lounge-chair_03.webp"]', NULL
WHERE @home_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Vitra Eames Lounge Chair' AND image = 'lux_20260531_home_vitra-eames-lounge-chair_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Cassina LC4 Chaise Longue', @home_id, 'A luxury chaise longue demo listing with architectural seating positioning, relaxed silhouette, and high-end home styling. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 6200.00, 'lux_20260531_home_cassina-lc4-chaise-longue_01.webp', 12, '["lux_20260531_home_cassina-lc4-chaise-longue_02.webp", "lux_20260531_home_cassina-lc4-chaise-longue_03.webp"]', NULL
WHERE @home_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Cassina LC4 Chaise Longue' AND image = 'lux_20260531_home_cassina-lc4-chaise-longue_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Hermes Avalon Blanket', @home_id, 'A wool-cashmere blanket demo listing with refined living-room positioning, soft texture styling, and gift-ready luxury appeal. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 1925.00, 'lux_20260531_home_hermes-avalon-blanket_01.webp', 12, '["lux_20260531_home_hermes-avalon-blanket_02.webp", "lux_20260531_home_hermes-avalon-blanket_03.webp"]', NULL
WHERE @home_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Hermes Avalon Blanket' AND image = 'lux_20260531_home_hermes-avalon-blanket_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Le Creuset Signature Dutch Oven', @home_id, 'A cast-iron cookware demo listing with heirloom kitchen positioning, polished cooking presentation, and transparent gallery assets. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 420.00, 'lux_20260531_home_le-creuset-signature-dutch-oven_01.webp', 12, '["lux_20260531_home_le-creuset-signature-dutch-oven_02.webp", "lux_20260531_home_le-creuset-signature-dutch-oven_03.webp"]', NULL
WHERE @home_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Le Creuset Signature Dutch Oven' AND image = 'lux_20260531_home_le-creuset-signature-dutch-oven_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'KitchenAid Artisan Stand Mixer', @home_id, 'A countertop mixer demo listing with baking-studio positioning, iconic appliance styling, and clean product imagery. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 449.99, 'lux_20260531_home_kitchenaid-artisan-stand-mixer_01.webp', 12, '["lux_20260531_home_kitchenaid-artisan-stand-mixer_02.webp", "lux_20260531_home_kitchenaid-artisan-stand-mixer_03.webp"]', NULL
WHERE @home_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'KitchenAid Artisan Stand Mixer' AND image = 'lux_20260531_home_kitchenaid-artisan-stand-mixer_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Dyson Purifier Big+Quiet', @home_id, 'A premium air purifier demo listing with quiet-room positioning, modern appliance styling, and local transparent images. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 999.99, 'lux_20260531_home_dyson-purifier-big-plus-quiet_01.webp', 12, '["lux_20260531_home_dyson-purifier-big-plus-quiet_02.webp", "lux_20260531_home_dyson-purifier-big-plus-quiet_03.webp"]', NULL
WHERE @home_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Dyson Purifier Big+Quiet' AND image = 'lux_20260531_home_dyson-purifier-big-plus-quiet_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Chanel N5 Eau de Parfum', @beauty_id, 'A classic fragrance demo listing with timeless scent positioning, elegant bottle styling, and premium transparent product images. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 172.00, 'lux_20260531_beauty_chanel-n5-eau-de-parfum_01.webp', 12, '["lux_20260531_beauty_chanel-n5-eau-de-parfum_02.webp", "lux_20260531_beauty_chanel-n5-eau-de-parfum_03.webp"]', NULL
WHERE @beauty_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Chanel N5 Eau de Parfum' AND image = 'lux_20260531_beauty_chanel-n5-eau-de-parfum_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Dior Sauvage Elixir', @beauty_id, 'A concentrated fragrance demo listing with bold scent positioning, polished bottle presentation, and clean gallery assets. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 180.00, 'lux_20260531_beauty_dior-sauvage-elixir_01.webp', 12, '["lux_20260531_beauty_dior-sauvage-elixir_02.webp", "lux_20260531_beauty_dior-sauvage-elixir_03.webp"]', NULL
WHERE @beauty_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Dior Sauvage Elixir' AND image = 'lux_20260531_beauty_dior-sauvage-elixir_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Tom Ford Oud Wood', @beauty_id, 'A private-blend fragrance demo listing with warm wood positioning, minimalist bottle styling, and luxury catalog imagery. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 445.00, 'lux_20260531_beauty_tom-ford-oud-wood_01.webp', 12, '["lux_20260531_beauty_tom-ford-oud-wood_02.webp", "lux_20260531_beauty_tom-ford-oud-wood_03.webp"]', NULL
WHERE @beauty_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Tom Ford Oud Wood' AND image = 'lux_20260531_beauty_tom-ford-oud-wood_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Creed Aventus', @beauty_id, 'A premium fragrance demo listing with signature-scent positioning, refined bottle styling, and transparent gallery views. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 495.00, 'lux_20260531_beauty_creed-aventus_01.webp', 12, '["lux_20260531_beauty_creed-aventus_02.webp", "lux_20260531_beauty_creed-aventus_03.webp"]', NULL
WHERE @beauty_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Creed Aventus' AND image = 'lux_20260531_beauty_creed-aventus_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Maison Francis Kurkdjian Baccarat Rouge 540', @beauty_id, 'A luxury fragrance demo listing with amber-floral positioning, collectible bottle styling, and polished product imagery. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 325.00, 'lux_20260531_beauty_maison-francis-kurkdjian-baccarat-rouge-540_01.webp', 12, '["lux_20260531_beauty_maison-francis-kurkdjian-baccarat-rouge-540_02.webp", "lux_20260531_beauty_maison-francis-kurkdjian-baccarat-rouge-540_03.webp"]', NULL
WHERE @beauty_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Maison Francis Kurkdjian Baccarat Rouge 540' AND image = 'lux_20260531_beauty_maison-francis-kurkdjian-baccarat-rouge-540_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'La Mer Creme de la Mer', @beauty_id, 'A prestige moisturizer demo listing with rich cream positioning, skincare ritual styling, and clean transparent assets. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 390.00, 'lux_20260531_beauty_la-mer-creme-de-la-mer_01.webp', 12, '["lux_20260531_beauty_la-mer-creme-de-la-mer_02.webp", "lux_20260531_beauty_la-mer-creme-de-la-mer_03.webp"]', NULL
WHERE @beauty_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'La Mer Creme de la Mer' AND image = 'lux_20260531_beauty_la-mer-creme-de-la-mer_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'La Prairie Skin Caviar Luxe Cream', @beauty_id, 'A luxury face cream demo listing with firming-care positioning, premium jar styling, and gallery-ready product images. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 590.00, 'lux_20260531_beauty_la-prairie-skin-caviar-luxe-cream_01.webp', 12, '["lux_20260531_beauty_la-prairie-skin-caviar-luxe-cream_02.webp", "lux_20260531_beauty_la-prairie-skin-caviar-luxe-cream_03.webp"]', NULL
WHERE @beauty_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'La Prairie Skin Caviar Luxe Cream' AND image = 'lux_20260531_beauty_la-prairie-skin-caviar-luxe-cream_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'SK-II Facial Treatment Essence', @beauty_id, 'A facial essence demo listing with skincare-icon positioning, refined bottle presentation, and three transparent gallery views. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 245.00, 'lux_20260531_beauty_sk-ii-facial-treatment-essence_01.webp', 12, '["lux_20260531_beauty_sk-ii-facial-treatment-essence_02.webp", "lux_20260531_beauty_sk-ii-facial-treatment-essence_03.webp"]', NULL
WHERE @beauty_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'SK-II Facial Treatment Essence' AND image = 'lux_20260531_beauty_sk-ii-facial-treatment-essence_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Cle de Peau Beaute The Serum', @beauty_id, 'A luxury serum demo listing with radiance-care positioning, slim bottle styling, and clean cutout imagery. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 295.00, 'lux_20260531_beauty_cle-de-peau-beaute-the-serum_01.webp', 12, '["lux_20260531_beauty_cle-de-peau-beaute-the-serum_02.webp", "lux_20260531_beauty_cle-de-peau-beaute-the-serum_03.webp"]', NULL
WHERE @beauty_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Cle de Peau Beaute The Serum' AND image = 'lux_20260531_beauty_cle-de-peau-beaute-the-serum_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Guerlain Orchidee Imperiale Cream', @beauty_id, 'A prestige face cream demo listing with orchid-inspired positioning, premium cosmetic styling, and transparent catalog assets. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 575.00, 'lux_20260531_beauty_guerlain-orchidee-imperiale-cream_01.webp', 12, '["lux_20260531_beauty_guerlain-orchidee-imperiale-cream_02.webp", "lux_20260531_beauty_guerlain-orchidee-imperiale-cream_03.webp"]', NULL
WHERE @beauty_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Guerlain Orchidee Imperiale Cream' AND image = 'lux_20260531_beauty_guerlain-orchidee-imperiale-cream_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Augustinus Bader The Rich Cream', @beauty_id, 'A high-performance moisturizer demo listing with rich-care positioning, refined bottle styling, and polished product presentation. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 300.00, 'lux_20260531_beauty_augustinus-bader-the-rich-cream_01.webp', 12, '["lux_20260531_beauty_augustinus-bader-the-rich-cream_02.webp", "lux_20260531_beauty_augustinus-bader-the-rich-cream_03.webp"]', NULL
WHERE @beauty_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Augustinus Bader The Rich Cream' AND image = 'lux_20260531_beauty_augustinus-bader-the-rich-cream_01.webp');

INSERT INTO products (name, category_id, description, price, image, stock, additional_images, video)
SELECT 'Jo Malone Lime Basil & Mandarin', @beauty_id, 'A luxury cologne demo listing with bright citrus-herbal positioning, elegant bottle styling, and clean gallery imagery. Seeded as demo inventory; verify official availability, licensing, and specifications before commercial sale.', 165.00, 'lux_20260531_beauty_jo-malone-lime-basil-and-mandarin_01.webp', 12, '["lux_20260531_beauty_jo-malone-lime-basil-and-mandarin_02.webp", "lux_20260531_beauty_jo-malone-lime-basil-and-mandarin_03.webp"]', NULL
WHERE @beauty_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Jo Malone Lime Basil & Mandarin' AND image = 'lux_20260531_beauty_jo-malone-lime-basil-and-mandarin_01.webp');
