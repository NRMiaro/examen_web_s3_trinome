-- Ajouter le besoin "Argent" de type argent s'il n'existe pas
INSERT IGNORE INTO s3_besoin (id_type_besoin, nom, prix) 
VALUES (3, 'Argent', 0);
