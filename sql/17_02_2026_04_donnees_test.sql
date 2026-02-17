-- =============================================================
-- Données de test pour tester le flux complet :
-- Simulation → Validation → Achats → Dashboard
-- =============================================================
-- ATTENTION : À exécuter APRÈS tous les autres scripts SQL
-- La base doit déjà contenir les tables et vues créées précédemment
-- =============================================================

-- =============================================
-- ÉTAT INITIAL (déjà dans 16_02_2026_01.sql) :
-- Villes : Antananarivo (id=1), Mahajanga (id=2)
-- Besoins : Riz id=1 (1000 Ar/kg), Huile id=2 (4000 Ar/L)
-- Demandes :
--   Tana : 1000kg Riz + 500L Huile (besoin_ville id=1)
--   Mahajanga : 2000kg Riz + 1000L Huile (besoin_ville id=2)
-- Dons matériels : 2500kg Riz + 1200L Huile
-- =============================================

-- 1. Ajouter des dons financiers (argent dans la caisse)
INSERT INTO s3_don_financier (montant, date_don) VALUES 
    (5000000, '2026-02-16 15:00:00'),   -- 5 000 000 Ar
    (3000000, '2026-02-16 16:00:00');   -- 3 000 000 Ar
-- Total caisse = 8 000 000 Ar

-- 2. Ajouter une 3e ville
INSERT INTO s3_ville (nom) VALUES ('Toamasina');  -- id=3

-- 3. Ajouter une demande pour Toamasina
INSERT INTO s3_besoin_ville (id_ville, date_besoin) 
VALUES (3, '2026-02-17 08:00:00');  -- id=3

INSERT INTO s3_besoin_ville_details (id_besoin_ville, id_besoin, quantite) VALUES 
    (3, 1, 500),   -- Toamasina demande 500kg Riz
    (3, 2, 300);   -- Toamasina demande 300L Huile

-- =============================================
-- RÉSUMÉ APRÈS INSERTION :
-- =============================================
-- 
-- DONS MATÉRIELS :
--   Riz  = 2500 kg
--   Huile = 1200 L
--
-- DEMANDES (par ordre chronologique) :
--   1. Tana (16/02 10h)      : 1000kg Riz + 500L Huile
--   2. Mahajanga (16/02 12h) : 2000kg Riz + 1000L Huile
--   3. Toamasina (17/02 08h) : 500kg Riz  + 300L Huile
--
-- TOTAUX DEMANDÉS :
--   Riz  = 1000 + 2000 + 500 = 3500 kg
--   Huile = 500 + 1000 + 300 = 1800 L
--
-- CAISSE : 8 000 000 Ar
-- =============================================
