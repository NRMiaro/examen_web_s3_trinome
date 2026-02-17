-- =============================================================
-- Insertion des demandes de besoins par ville
-- Date : 17/02/2026
-- =============================================================
-- Données :
--   5 villes : Toamasina, Mananjary, Farafangana, Nosy Be, Morondava
--   10 besoins : Riz (kg), Eau (L), Huile (L), Haricots, Tôle, Bâche, Clous (kg), Bois, groupe, Argent
--   26 lignes de demandes réparties sur 2 dates (15 et 16 février 2026)
--   Chaque demande a un ordre global de soumission (colonne 'ordre')
-- =============================================================
-- PRÉREQUIS : Exécuter 17_02_2026_06_argent_et_ordre.sql AVANT ce script
--   (ajout type 'argent' + colonne 'ordre' dans s3_besoin_ville_details)
-- =============================================================

-- =============================================
-- 1. VILLES
-- =============================================
INSERT IGNORE INTO s3_ville (nom) VALUES 
    ('Toamasina'),
    ('Mananjary'),
    ('Farafangana'),
    ('Nosy Be'),
    ('Morondava');

-- =============================================
-- 2. TYPES DE BESOIN
-- =============================================
INSERT IGNORE INTO s3_type_besoin (nom) VALUES ('nature');
INSERT IGNORE INTO s3_type_besoin (nom) VALUES ('matériel');
INSERT IGNORE INTO s3_type_besoin (nom) VALUES ('argent');

-- =============================================
-- 3. BESOINS
-- =============================================
-- Nature
INSERT INTO s3_besoin (id_type_besoin, nom, prix) VALUES 
    ((SELECT id FROM s3_type_besoin WHERE nom = 'nature'), 'Riz (kg)', 3000),
    ((SELECT id FROM s3_type_besoin WHERE nom = 'nature'), 'Eau (L)', 1000),
    ((SELECT id FROM s3_type_besoin WHERE nom = 'nature'), 'Huile (L)', 6000),
    ((SELECT id FROM s3_type_besoin WHERE nom = 'nature'), 'Haricots', 4000);

-- Matériel
INSERT INTO s3_besoin (id_type_besoin, nom, prix) VALUES 
    ((SELECT id FROM s3_type_besoin WHERE nom = 'matériel'), 'Tôle', 25000),
    ((SELECT id FROM s3_type_besoin WHERE nom = 'matériel'), 'Bâche', 15000),
    ((SELECT id FROM s3_type_besoin WHERE nom = 'matériel'), 'Clous (kg)', 8000),
    ((SELECT id FROM s3_type_besoin WHERE nom = 'matériel'), 'Bois', 10000),
    ((SELECT id FROM s3_type_besoin WHERE nom = 'matériel'), 'groupe', 6750000);

-- Argent (prix = 1 car l'unité est l'Ariary)
INSERT INTO s3_besoin (id_type_besoin, nom, prix) VALUES 
    ((SELECT id FROM s3_type_besoin WHERE nom = 'argent'), 'Argent', 1);

-- =============================================
-- 4. VARIABLES POUR LES IDs
-- =============================================

-- IDs des villes
SET @toamasina  = (SELECT id FROM s3_ville WHERE nom = 'Toamasina');
SET @mananjary  = (SELECT id FROM s3_ville WHERE nom = 'Mananjary');
SET @farafangana = (SELECT id FROM s3_ville WHERE nom = 'Farafangana');
SET @nosy_be    = (SELECT id FROM s3_ville WHERE nom = 'Nosy Be');
SET @morondava  = (SELECT id FROM s3_ville WHERE nom = 'Morondava');

-- IDs des besoins
SET @riz     = (SELECT id FROM s3_besoin WHERE nom = 'Riz (kg)' LIMIT 1);
SET @eau     = (SELECT id FROM s3_besoin WHERE nom = 'Eau (L)' LIMIT 1);
SET @huile   = (SELECT id FROM s3_besoin WHERE nom = 'Huile (L)' LIMIT 1);
SET @haricots = (SELECT id FROM s3_besoin WHERE nom = 'Haricots' LIMIT 1);
SET @tole    = (SELECT id FROM s3_besoin WHERE nom = 'Tôle' LIMIT 1);
SET @bache   = (SELECT id FROM s3_besoin WHERE nom = 'Bâche' LIMIT 1);
SET @clous   = (SELECT id FROM s3_besoin WHERE nom = 'Clous (kg)' LIMIT 1);
SET @bois    = (SELECT id FROM s3_besoin WHERE nom = 'Bois' LIMIT 1);
SET @groupe  = (SELECT id FROM s3_besoin WHERE nom = 'groupe' LIMIT 1);
SET @argent  = (SELECT id FROM s3_besoin WHERE nom = 'Argent' LIMIT 1);

-- =============================================
-- 5. DEMANDES PAR VILLE (s3_besoin_ville + s3_besoin_ville_details avec ordre)
-- =============================================
-- L'ordre est global : il détermine la priorité de traitement dans la simulation
-- Ordre 1 = première demande soumise, Ordre 26 = dernière

-- ----- TOAMASINA - 15/02/2026 -----
INSERT INTO s3_besoin_ville (id_ville, date_besoin) VALUES (@toamasina, '2026-02-15');
SET @bv = LAST_INSERT_ID();
INSERT INTO s3_besoin_ville_details (id_besoin_ville, id_besoin, quantite, ordre) VALUES 
    (@bv, @eau, 1500, 4),
    (@bv, @bache, 200, 1),
    (@bv, @groupe, 3, 16);

-- ----- TOAMASINA - 16/02/2026 -----
INSERT INTO s3_besoin_ville (id_ville, date_besoin) VALUES (@toamasina, '2026-02-16');
SET @bv = LAST_INSERT_ID();
INSERT INTO s3_besoin_ville_details (id_besoin_ville, id_besoin, quantite, ordre) VALUES 
    (@bv, @riz, 800, 17),
    (@bv, @tole, 120, 23),
    (@bv, @argent, 12000000, 12);

-- ----- MANANJARY - 15/02/2026 -----
INSERT INTO s3_besoin_ville (id_ville, date_besoin) VALUES (@mananjary, '2026-02-15');
SET @bv = LAST_INSERT_ID();
INSERT INTO s3_besoin_ville_details (id_besoin_ville, id_besoin, quantite, ordre) VALUES 
    (@bv, @riz, 500, 9),
    (@bv, @tole, 80, 6),
    (@bv, @argent, 6000000, 3);

-- ----- MANANJARY - 16/02/2026 -----
INSERT INTO s3_besoin_ville (id_ville, date_besoin) VALUES (@mananjary, '2026-02-16');
SET @bv = LAST_INSERT_ID();
INSERT INTO s3_besoin_ville_details (id_besoin_ville, id_besoin, quantite, ordre) VALUES 
    (@bv, @huile, 120, 25),
    (@bv, @clous, 60, 19);

-- ----- FARAFANGANA - 15/02/2026 -----
INSERT INTO s3_besoin_ville (id_ville, date_besoin) VALUES (@farafangana, '2026-02-15');
SET @bv = LAST_INSERT_ID();
INSERT INTO s3_besoin_ville_details (id_besoin_ville, id_besoin, quantite, ordre) VALUES 
    (@bv, @eau, 1000, 14),
    (@bv, @bois, 100, 26);

-- ----- FARAFANGANA - 16/02/2026 -----
INSERT INTO s3_besoin_ville (id_ville, date_besoin) VALUES (@farafangana, '2026-02-16');
SET @bv = LAST_INSERT_ID();
INSERT INTO s3_besoin_ville_details (id_besoin_ville, id_besoin, quantite, ordre) VALUES 
    (@bv, @riz, 600, 21),
    (@bv, @bache, 150, 8),
    (@bv, @argent, 8000000, 10);

-- ----- NOSY BE - 15/02/2026 -----
INSERT INTO s3_besoin_ville (id_ville, date_besoin) VALUES (@nosy_be, '2026-02-15');
SET @bv = LAST_INSERT_ID();
INSERT INTO s3_besoin_ville_details (id_besoin_ville, id_besoin, quantite, ordre) VALUES 
    (@bv, @riz, 300, 5),
    (@bv, @argent, 4000000, 7);

-- ----- NOSY BE - 16/02/2026 -----
INSERT INTO s3_besoin_ville (id_ville, date_besoin) VALUES (@nosy_be, '2026-02-16');
SET @bv = LAST_INSERT_ID();
INSERT INTO s3_besoin_ville_details (id_besoin_ville, id_besoin, quantite, ordre) VALUES 
    (@bv, @haricots, 200, 18),
    (@bv, @tole, 40, 2),
    (@bv, @clous, 30, 24);

-- ----- MORONDAVA - 15/02/2026 -----
INSERT INTO s3_besoin_ville (id_ville, date_besoin) VALUES (@morondava, '2026-02-15');
SET @bv = LAST_INSERT_ID();
INSERT INTO s3_besoin_ville_details (id_besoin_ville, id_besoin, quantite, ordre) VALUES 
    (@bv, @eau, 1200, 20),
    (@bv, @bois, 150, 22);

-- ----- MORONDAVA - 16/02/2026 -----
INSERT INTO s3_besoin_ville (id_ville, date_besoin) VALUES (@morondava, '2026-02-16');
SET @bv = LAST_INSERT_ID();
INSERT INTO s3_besoin_ville_details (id_besoin_ville, id_besoin, quantite, ordre) VALUES 
    (@bv, @riz, 700, 11),
    (@bv, @bache, 180, 15),
    (@bv, @argent, 10000000, 13);

-- =============================================
-- RÉSUMÉ DES DONNÉES INSÉRÉES
-- =============================================
--
-- VILLES (5) : Toamasina, Mananjary, Farafangana, Nosy Be, Morondava
--
-- BESOINS (10) :
--   Nature   : Riz (kg) 3000 Ar, Eau (L) 1000 Ar, Huile (L) 6000 Ar, Haricots 4000 Ar
--   Matériel : Tôle 25000 Ar, Bâche 15000 Ar, Clous (kg) 8000 Ar, Bois 10000 Ar, groupe 6 750 000 Ar
--   Argent   : Argent 1 Ar (unité = ariary, stock = solde caisse)
--
-- DEMANDES (10 besoin_ville, 26 détails avec ordre de soumission) :
--   Toamasina   : 15/02 → Eau 1500 (o4), Bâche 200 (o1), groupe 3 (o16)
--                  16/02 → Riz 800 (o17), Tôle 120 (o23), Argent 12M (o12)
--   Mananjary   : 15/02 → Riz 500 (o9), Tôle 80 (o6), Argent 6M (o3)
--                  16/02 → Huile 120 (o25), Clous 60 (o19)
--   Farafangana : 15/02 → Eau 1000 (o14), Bois 100 (o26)
--                  16/02 → Riz 600 (o21), Bâche 150 (o8), Argent 8M (o10)
--   Nosy Be     : 15/02 → Riz 300 (o5), Argent 4M (o7)
--                  16/02 → Haricots 200 (o18), Tôle 40 (o2), Clous 30 (o24)
--   Morondava   : 15/02 → Eau 1200 (o20), Bois 150 (o22)
--                  16/02 → Riz 700 (o11), Bâche 180 (o15), Argent 10M (o13)
--
-- TOTAL ARGENT DEMANDÉ : 40 000 000 Ar
--
-- ORDRE CHRONOLOGIQUE DES DEMANDES :
--   1.Bâche(Toam) 2.Tôle(NosyBe) 3.Argent(Mananj) 4.Eau(Toam) 5.Riz(NosyBe)
--   6.Tôle(Mananj) 7.Argent(NosyBe) 8.Bâche(Faraf) 9.Riz(Mananj) 10.Argent(Faraf)
--   11.Riz(Moron) 12.Argent(Toam) 13.Argent(Moron) 14.Eau(Faraf) 15.Bâche(Moron)
--   16.groupe(Toam) 17.Riz(Toam) 18.Haricots(NosyBe) 19.Clous(Mananj) 20.Eau(Moron)
--   21.Riz(Faraf) 22.Bois(Moron) 23.Tôle(Toam) 24.Clous(NosyBe) 25.Huile(Mananj)
--   26.Bois(Faraf)
-- =============================================
