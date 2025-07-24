-- Insertion des catégories de produits
INSERT INTO product_categories (id, name, slug, description) VALUES
(1, 'Meubles', 'meubles', 'Meubles de qualité pour votre intérieur'),
(2, 'Tapis', 'tapis', 'Tapis modernes et traditionnels'),
(3, 'Luminaires', 'luminaires', 'Solutions d\'éclairage élégantes'),
(4, 'Textiles', 'textiles', 'Textiles de maison de qualité'),
(5, 'Décoration murale', 'decoration-murale', 'Éléments décoratifs pour vos murs'),
(6, 'Accessoires', 'accessoires', 'Accessoires de décoration pour la maison');

-- Insertion des produits
INSERT INTO products (id, category_id, name, slug, description, price, stock, image) VALUES
(1, 1, 'Canapé en velours vert', 'canape-en-velours-vert', 'Ce canapé en velours vert émeraude apporte une touche d\'élégance et de confort à votre salon.', 600000.00, 10, 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'),
(2, 2, 'Tapis moderne géométrique', 'tapis-moderne-geometrique', 'Tapis moderne avec motifs géométriques pour égayer votre sol.', 160000.00, 15, 'https://i.pinimg.com/1200x/4d/44/a2/4d44a23e9d55c58ab9e294f6851b4731.jpg'),
(3, 3, 'Lampe suspendue en rotin', 'lampe-suspendue-en-rotin', 'Éclairez votre intérieur avec cette lampe naturelle et chaleureuse.', 100000.00, 20, 'https://i.pinimg.com/736x/8c/b6/f9/8cb6f9e848550e8d0aaf643c3df91dc3.jpg'),
(4, 1, 'Table basse scandinave', 'table-basse-scandinave', 'Design épuré et bois clair pour un intérieur nordique.', 210000.00, 8, 'https://i.pinimg.com/736x/c9/a0/27/c9a027b4532d8b722fc0ff1c7b3debb8.jpg'),
(5, 4, 'Coussins décoratifs en lin', 'coussins-decoratifs-en-lin', 'Ajoutez du confort et du style avec nos coussins 100% lin.', 30000.00, 30, 'https://i.pinimg.com/736x/a4/f7/7f/a4f77faedb1cb71183c0f7de611b83b7.jpg'),
(6, 4, 'Rideaux occultants beige', 'rideaux-occultants-beige', 'Parfaits pour une ambiance cosy et intime.', 60000.00, 25, 'https://i.pinimg.com/1200x/2e/05/a2/2e05a2d6a227d95c91a9ea2d606ca70c.jpg'),
(7, 1, 'Bibliothèque murale minimaliste', 'bibliotheque-murale-minimaliste', 'Optimisez l\'espace avec cette étagère au design moderne.', 400000.00, 5, 'https://i.pinimg.com/1200x/da/d4/a0/dad4a05624abb1f1dd6e93886e72bfc2.jpg'),
(8, 5, 'Tableau abstrait coloré', 'tableau-abstrait-colore', 'Une explosion de couleurs pour dynamiser votre pièce.', 120000.00, 12, 'https://i.pinimg.com/1200x/c2/49/a9/c249a9a569b486706c47c2c89b49cfb5.jpg'),
(9, 6, 'Horloge murale vintage', 'horloge-murale-vintage', 'Un charme rétro pour votre mur.', 90000.00, 18, 'https://i.pinimg.com/1200x/1f/b5/3f/1fb53f95e037186889dfaeefdd2a488a.jpg'),
(10, 1, 'Chaise design en bois clair', 'chaise-design-en-bois-clair', 'Chaise élégante et confortable pour salle à manger ou bureau.', 130000.00, 20, 'https://i.pinimg.com/1200x/25/2d/47/252d47329c45e2aaf1f64ce9ce17f557.jpg');