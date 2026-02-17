-- =============================================================
-- Script SQL combiné (hormis le dernier)
-- =============================================================

-- ===========================
-- 16_02_2026_01.sql
-- ===========================
CREATE TABLE s3_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_type_besoin INT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    prix INT NOT NULL
);

CREATE TABLE s3_ville (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL
);

CREATE TABLE s3_besoin_ville (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_ville INT NOT NULL,
    date_besoin DATETIME NOT NULL,
    FOREIGN KEY (id_ville) REFERENCES s3_ville(id)
);

CREATE TABLE s3_besoin_ville_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_besoin_ville INT NOT NULL,
    id_besoin INT NOT NULL,
    quantite INT NOT NULL,
    FOREIGN KEY (id_besoin_ville) REFERENCES s3_besoin_ville(id),
    FOREIGN KEY (id_besoin) REFERENCES s3_besoin(id)
);

CREATE TABLE s3_don (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_don DATETIME NOT NULL
);

CREATE TABLE s3_don_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_don INT NOT NULL,
    id_besoin INT NOT NULL,
    quantite INT NOT NULL,
    FOREIGN KEY (id_don) REFERENCES s3_don(id),
    FOREIGN KEY (id_besoin) REFERENCES s3_besoin(id)
);
-- ===========================
-- 16_02_2026_03.sql
-- ===========================
CREATE TABLE s3_dispatch_validation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_besoin_ville INT NOT NULL,
    id_besoin INT NOT NULL,
    quantite_demandee INT NOT NULL,
    quantite_allouee INT NOT NULL,
    quantite_manquante INT NOT NULL,
    statut ENUM('resolved', 'partial', 'unresolved') NOT NULL,
    date_validation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_besoin_ville) REFERENCES s3_besoin_ville(id),
    FOREIGN KEY (id_besoin) REFERENCES s3_besoin(id)
);

-- ===========================
-- 16_02_2026_02-vues_dispatch.sql
-- ===========================
CREATE OR REPLACE VIEW v_dispatch_validation AS
SELECT 
    dv.id,
    dv.id_besoin_ville,
    dv.id_besoin,
    bv.id_ville,
    v.nom AS ville_nom,
    b.nom AS besoin_nom,
    dv.quantite_demandee,
    dv.quantite_allouee,
    dv.quantite_manquante,
    dv.statut,
    dv.date_validation,
    ROUND((dv.quantite_allouee / dv.quantite_demandee * 100), 0) AS pourcentage_couverture
FROM s3_dispatch_validation dv
JOIN s3_besoin_ville bv ON dv.id_besoin_ville = bv.id
JOIN s3_ville v ON bv.id_ville = v.id
JOIN s3_besoin b ON dv.id_besoin = b.id
ORDER BY dv.date_validation DESC, b.nom;


-- ===========================
-- 16_02_2026_04.sql
-- ===========================
CREATE TABLE s3_achat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_besoin INT NOT NULL,
    id_ville INT NOT NULL,
    quantite INT NOT NULL,
    prix_total INT NOT NULL,
    date_achat DATETIME NOT NULL,
    FOREIGN KEY (id_besoin) REFERENCES s3_besoin(id),
    FOREIGN KEY (id_ville) REFERENCES s3_ville(id)
);

-- ===========================
-- 17_02_2026_01_achats.sql
-- ===========================
CREATE OR REPLACE VIEW v_achats AS
SELECT 
    a.id,
    v.nom AS ville_nom,
    b.nom AS besoin_nom,
    a.quantite,
    a.prix_total,
    a.date_achat
FROM s3_achat a
JOIN s3_ville v ON a.id_ville = v.id
JOIN s3_besoin b ON a.id_besoin = b.id;

-- ===========================
-- 17_02_2026_02_separation_dons.sql
-- ===========================
CREATE OR REPLACE VIEW v_dons_disponibles AS
SELECT 
    b.nom AS besoin_nom,
    SUM(dd.quantite) AS quantite_disponible
FROM s3_don_details dd
JOIN s3_besoin b ON dd.id_besoin = b.id
GROUP BY b.nom;

-- ===========================
-- 17_02_2026_03-simulation&validation.sql
-- ===========================
CREATE OR REPLACE VIEW v_dispatch_stats AS
SELECT 
    COUNT(*) AS total_allocations,
    SUM(CASE WHEN statut = 'resolved' THEN 1 ELSE 0 END) AS allocations_completes,
    SUM(CASE WHEN statut = 'partial' THEN 1 ELSE 0 END) AS allocations_partielles,
    SUM(CASE WHEN statut = 'unresolved' THEN 1 ELSE 0 END) AS allocations_invalides,
    ROUND(SUM(CASE WHEN statut = 'resolved' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) AS pourcentage_resolu
FROM s3_dispatch_validation;

-- ===========================
-- 17_02_2026_04_donnees_test.sql
-- ===========================
-- Insérer des besoins
INSERT INTO s3_besoin (id_type_besoin, nom, prix) VALUES
    (1, 'Riz', 1000),
    (1, 'Huile', 4000);

-- Insérer des villes
INSERT INTO s3_ville (nom) VALUES
    ('Antananarivo'),
    ('Mahajanga'),
    ('Toamasina');

-- Insérer des besoins par ville
INSERT INTO s3_besoin_ville (id_ville, date_besoin) VALUES
    (1, '2026-02-16 10:00:00'),
    (2, '2026-02-16 12:00:00'),
    (3, '2026-02-17 08:00:00');

-- Insérer des détails des besoins par ville
INSERT INTO s3_besoin_ville_details (id_besoin_ville, id_besoin, quantite) VALUES
    (1, 1, 1000),
    (1, 2, 500),
    (2, 1, 2000),
    (2, 2, 1000),
    (3, 1, 500),
    (3, 2, 300);

-- Insérer des dons
INSERT INTO s3_don (date_don) VALUES
    ('2026-02-16 14:00:00');

-- Insérer des détails des dons
INSERT INTO s3_don_details (id_don, id_besoin, quantite) VALUES
    (1, 1, 2500),
    (1, 2, 1200);