-- Décommenter ça si en local

DROP DATABASE IF EXISTS trinome_bngrc;
CREATE DATABASE trinome_bngrc;

-- 

CREATE TABLE trinome_ville (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50)
);

INSERT INTO trinome_ville (nom) 
VALUES 
    ('Antananarivo'),
    ('Mahajanga');

CREATE TABLE trinome_type_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50)
);

INSERT INTO trinome_type_besoin (nom) 
VALUES 
    ('nature'),
    ('matériel'),
    ('argent');

CREATE TABLE trinome_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_type_besoin INT NOT NULL,
    nom VARCHAR(50) NOT NULL,
    prix INT NOT NULL,
    FOREIGN KEY (id_type_besoin) REFERENCES trinome_type_besoin(id)
);

CREATE TABLE trinome_besoin_ville (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_ville INT NOT NULL,
    id_besoin INT NOT NULL,
    quantite INT NOT NULL,
    FOREIGN KEY (id_ville) REFERENCES trinome_ville(id),
    FOREIGN KEY (id_besoin) REFERENCES trinome_besoin(id)
);

CREATE TABLE trinome_etat_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(50) NOT NULL
);

INSERT INTO trinome_etat_besoin (description) 
VALUES 
    ('En attente'),
    ('Délivré');

CREATE TABLE trinome_don (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_besoin_ville INT NOT NULL,
    FOREIGN KEY (id_besoin_ville) REFERENCES trinome_besoin_ville(id)
);

CREATE TABLE trinome_historique_etat_besoin_ville (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_besoin_ville INT NOT NULL,
    date_ DATETIME NOT NULL,
    id_etat_besoin INT NOT NULL,
    FOREIGN KEY (id_besoin_ville) REFERENCES trinome_besoin_ville(id),
    FOREIGN KEY (id_etat_besoin) REFERENCES trinome_etat_besoin(id)
);

-- Données de test minimales
-- Insertion de besoins supplémentaires
INSERT INTO trinome_besoin (id_type_besoin, nom, prix) 
VALUES 
    (1, 'Eau', 1000),
    (2, 'Couvertures', 5000),
    (1, 'Nourriture', 15000);

-- Insertion des besoins par ville
INSERT INTO trinome_besoin_ville (id_ville, id_besoin, quantite) 
VALUES 
    (1, 1, 100),  -- 100 litres d'eau à Antananarivo
    (1, 2, 50),   -- 50 couvertures à Antananarivo
    (2, 1, 150),  -- 150 litres d'eau à Mahajanga
    (2, 3, 200);  -- 200 portions de nourriture à Mahajanga

-- Insertion des dons
INSERT INTO trinome_don (id_besoin_ville) 
VALUES 
    (1),
    (3);

-- Insertion de l'historique des états
INSERT INTO trinome_historique_etat_besoin_ville (id_besoin_ville, date, id_etat_besoin) 
VALUES 
    (1, NOW(), 1),           -- Besoin 1 délivré (dans trinome_don)
    (1, NOW(), 2),           -- Besoin 1 délivré (dans trinome_don)
    (2, NOW(), 1),           -- Besoin 2 en attente
    (3, NOW(), 1),           -- Besoin 3 délivré (dans trinome_don)
    (3, NOW(), 2),           -- Besoin 3 délivré (dans trinome_don)
    (4, NOW(), 1);           -- Besoin 4 en attente