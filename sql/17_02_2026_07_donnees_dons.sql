-- =============================================================
-- Insertion des dons (financiers + nature/matériel)
-- Date : 17/02/2026
-- =============================================================
-- Données :
--   Dons financiers (5 entrées) → s3_don_financier
--   Dons nature/matériel (11 entrées) → s3_don + s3_don_details
-- =============================================================
-- ATTENTION : Exécuter APRÈS 17_02_2026_05 et 17_02_2026_06
--   (les besoins doivent déjà exister dans s3_besoin)
-- =============================================================

-- =============================================
-- 1. DONS FINANCIERS (s3_don_financier)
-- =============================================
INSERT INTO s3_don_financier (montant, date_don) VALUES 
    (5000000,  '2026-02-16'),  -- 5 000 000 Ar
    (3000000,  '2026-02-16'),  -- 3 000 000 Ar
    (4000000,  '2026-02-17'),  -- 4 000 000 Ar
    (1500000,  '2026-02-17'),  -- 1 500 000 Ar
    (6000000,  '2026-02-17'),  -- 6 000 000 Ar
    (20000000, '2026-02-19');  -- 20 000 000 Ar
-- Total dons financiers = 39 500 000 Ar

-- =============================================
-- 2. VARIABLES POUR LES IDs DES BESOINS
-- =============================================
SET @riz      = (SELECT id FROM s3_besoin WHERE nom = 'Riz (kg)' LIMIT 1);
SET @eau      = (SELECT id FROM s3_besoin WHERE nom = 'Eau (L)' LIMIT 1);
SET @tole     = (SELECT id FROM s3_besoin WHERE nom = 'Tôle' LIMIT 1);
SET @bache    = (SELECT id FROM s3_besoin WHERE nom = 'Bâche' LIMIT 1);
SET @haricots = (SELECT id FROM s3_besoin WHERE nom = 'Haricots' LIMIT 1);

-- =============================================
-- 3. DONS EN NATURE / MATÉRIEL (s3_don + s3_don_details)
-- =============================================

-- Don du 16/02/2026 : Riz 400 kg + Eau 600 L
INSERT INTO s3_don (date_don) VALUES ('2026-02-16');
SET @don = LAST_INSERT_ID();
INSERT INTO s3_don_details (id_don, id_besoin, quantite) VALUES 
    (@don, @riz, 400),
    (@don, @eau, 600);

-- Don du 17/02/2026 : Tôle 50 + Bâche 70 + Haricots 100 + Haricots 88
INSERT INTO s3_don (date_don) VALUES ('2026-02-17');
SET @don = LAST_INSERT_ID();
INSERT INTO s3_don_details (id_don, id_besoin, quantite) VALUES 
    (@don, @tole, 50),
    (@don, @bache, 70),
    (@don, @haricots, 100),
    (@don, @haricots, 88);

-- Don du 18/02/2026 : Riz 2000 kg + Tôle 300 + Eau 5000 L
INSERT INTO s3_don (date_don) VALUES ('2026-02-18');
SET @don = LAST_INSERT_ID();
INSERT INTO s3_don_details (id_don, id_besoin, quantite) VALUES 
    (@don, @riz, 2000),
    (@don, @tole, 300),
    (@don, @eau, 5000);

-- Don du 19/02/2026 : Bâche 500
INSERT INTO s3_don (date_don) VALUES ('2026-02-19');
SET @don = LAST_INSERT_ID();
INSERT INTO s3_don_details (id_don, id_besoin, quantite) VALUES 
    (@don, @bache, 500);

-- =============================================
-- RÉSUMÉ DES DONS INSÉRÉS
-- =============================================
--
-- DONS FINANCIERS (6 entrées) :
--   16/02 : 5 000 000 + 3 000 000 = 8 000 000 Ar
--   17/02 : 4 000 000 + 1 500 000 + 6 000 000 = 11 500 000 Ar
--   19/02 : 20 000 000 Ar
--   TOTAL : 39 500 000 Ar
--
-- DONS NATURE (par produit) :
--   Riz (kg)  : 400 + 2000 = 2 400 kg
--   Eau (L)   : 600 + 5000 = 5 600 L
--   Haricots  : 100 + 88 = 188 kg
--
-- DONS MATÉRIEL (par produit) :
--   Tôle      : 50 + 300 = 350 pcs
--   Bâche     : 70 + 500 = 570 pcs
--
-- =============================================
