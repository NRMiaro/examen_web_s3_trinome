-- Décommenter ça si en local

DROP DATABASE IF EXISTS s3_bngrc;
CREATE DATABASE s3_bngrc;
use s3_bngrc;
-- 

CREATE TABLE s3_ville (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50)
);

INSERT INTO s3_ville (nom) 
VALUES 
    ('Antananarivo'),
    ('Mahajanga');

CREATE TABLE s3_type_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50)
);

INSERT INTO s3_type_besoin (nom) 
VALUES 
    ('nature'),
    ('matériel'),
    ('argent');

CREATE TABLE s3_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_type_besoin INT NOT NULL,
    nom VARCHAR(50) NOT NULL,
    prix INT NOT NULL,
    FOREIGN KEY (id_type_besoin) REFERENCES s3_type_besoin(id)
);

INSERT INTO s3_besoin (id_type_besoin, nom, prix) 
VALUES 
    (1, 'Riz', 1000),
    (1, 'Huile', 4000);


-- Une ville fait une demande (on stocke les détails dans s3_besoin_ville_details)
CREATE TABLE s3_besoin_ville (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_ville INT NOT NULL,
    date_besoin datetime,
    FOREIGN KEY (id_ville) REFERENCES s3_ville(id)
);

INSERT INTO s3_besoin_ville 
    (id_ville, date_besoin)
VALUES 
    (1, '2026-02-16 10:00:00'),
    (2, '2026-02-16 12:00:00');

-- Détails d'une demande de besoins
CREATE TABLE s3_besoin_ville_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_besoin_ville INT NOT NULL,
    id_besoin INT NOT NULL,
    quantite INT NOT NULL,
    FOREIGN KEY (id_besoin_ville) REFERENCES s3_besoin_ville(id),
    FOREIGN KEY (id_besoin) REFERENCES s3_besoin(id)
);

INSERT INTO s3_besoin_ville_details 
    (id_besoin_ville, id_besoin, quantite)
VALUES 
    (1, 1, 1000), -- tana demande 1000kg de riz
    (1, 2, 500),  -- tana demande 500L de huile
    (2, 1, 2000), -- mahajanga demande 2000kg de riz
    (2, 2, 1000);  -- mahajanga demande 1000L de huile


CREATE TABLE s3_don (
    id int AUTO_INCREMENT PRIMARY KEY,
    date_don datetime
);

INSERT INTO s3_don 
    (date_don)
VALUES 
    ('2026-02-16 14:00:00');

create table s3_don_details (
    id int AUTO_INCREMENT PRIMARY KEY,
    id_don int NOT NULL,
    id_besoin int not null,
    quantite int not null,
    FOREIGN KEY (id_don) REFERENCES s3_don(id),
    FOREIGN KEY (id_besoin) REFERENCES s3_besoin(id)
);

insert into s3_don_details 
    (id_don, id_besoin, quantite)
VALUES 
    (1, 1, 2500),
    (1, 2, 1200);