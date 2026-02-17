-- Décommenter ça si en local

DROP DATABASE IF EXISTS s3_bngrc;
CREATE DATABASE s3_bngrc;
use s3_bngrc;
-- 

CREATE TABLE s3_ville (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50)
);

-- Les données de s3_ville sont insérées via 17_02_2026_05_donnees_besoins_villes.sql

CREATE TABLE s3_type_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50)
);

-- Les données de s3_type_besoin sont insérées via 17_02_2026_05_donnees_besoins_villes.sql

CREATE TABLE s3_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_type_besoin INT NOT NULL,
    nom VARCHAR(50) NOT NULL,
    prix INT NOT NULL,
    FOREIGN KEY (id_type_besoin) REFERENCES s3_type_besoin(id)
);

-- Les données de s3_besoin sont insérées via 17_02_2026_05_donnees_besoins_villes.sql

-- Une ville fait une demande (on stocke les détails dans s3_besoin_ville_details)
CREATE TABLE s3_besoin_ville (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_ville INT NOT NULL,
    date_besoin datetime,
    FOREIGN KEY (id_ville) REFERENCES s3_ville(id)
);

-- Les données de s3_besoin_ville sont insérées via 17_02_2026_05_donnees_besoins_villes.sql

-- Détails d'une demande de besoins
CREATE TABLE s3_besoin_ville_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_besoin_ville INT NOT NULL,
    id_besoin INT NOT NULL,
    quantite INT NOT NULL,
    FOREIGN KEY (id_besoin_ville) REFERENCES s3_besoin_ville(id),
    FOREIGN KEY (id_besoin) REFERENCES s3_besoin(id)
);

-- Les données de s3_besoin_ville_details sont insérées via 17_02_2026_05_donnees_besoins_villes.sql

CREATE TABLE s3_don (
    id int AUTO_INCREMENT PRIMARY KEY,
    date_don datetime
);

-- Les données de test des dons ont été retirées

create table s3_don_details (
    id int AUTO_INCREMENT PRIMARY KEY,
    id_don int NOT NULL,
    id_besoin int not null,
    quantite int not null,
    FOREIGN KEY (id_don) REFERENCES s3_don(id),
    FOREIGN KEY (id_besoin) REFERENCES s3_besoin(id)
);

-- Les données de test des dons ont été retirées