-- =============================================================
-- Migration : 
--   1. Ajout du type de besoin 'argent' + besoin 'Argent' (prix=1 Ar)
--      → Les villes peuvent demander de l'argent comme un besoin
--      → La simulation puise dans le solde de la caisse
--      → Après validation, le solde réel de la caisse diminue
--   2. Ajout de la colonne 'ordre' dans s3_besoin_ville_details
--      → Ordre chronologique global de soumission des demandes
--      → Remplace la date comme critère de priorité dans le dispatch
-- =============================================================

-- =============================================
-- 1. TYPE DE BESOIN 'argent'
-- =============================================

-- =============================================
-- 2. COLONNE ORDRE dans s3_besoin_ville_details
-- =============================================
-- L'ordre est global (pas par ville ni par besoin) : il détermine qui passe en premier
ALTER TABLE s3_besoin_ville_details 
    ADD COLUMN ordre INT DEFAULT NULL AFTER quantite;

-- Index pour optimiser les tris par ordre
ALTER TABLE s3_besoin_ville_details 
    ADD INDEX idx_ordre (ordre);

-- =============================================
-- 3. METTRE À JOUR LA VUE v_dispatch_demandes
-- =============================================
-- Utiliser la colonne 'ordre' au lieu de date_besoin pour le tri
DROP VIEW IF EXISTS v_dispatch_demandes;
CREATE VIEW v_dispatch_demandes AS
    SELECT 
        bv.id AS id_besoin_ville,
        bv.date_besoin,
        v.id AS id_ville,
        v.nom AS ville_nom,
        b.id AS id_besoin,
        b.nom AS besoin_nom,
        bvd.quantite AS quantite_demandee,
        bvd.ordre,
        ROW_NUMBER() OVER (PARTITION BY b.id ORDER BY bvd.ordre, bv.date_besoin) AS ordre_demande
    FROM s3_besoin_ville bv
    JOIN s3_ville v ON bv.id_ville = v.id
    JOIN s3_besoin_ville_details bvd ON bvd.id_besoin_ville = bv.id
    JOIN s3_besoin b ON bvd.id_besoin = b.id
    ORDER BY bvd.ordre, b.nom;
