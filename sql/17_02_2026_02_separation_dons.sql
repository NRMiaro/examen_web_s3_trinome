-- =============================================================
-- Migration : Séparer les dons financiers des dons de biens
-- Argent ≠ Besoin → table dédiée s3_don_financier
-- =============================================================

-- 1. Créer la table dédiée aux dons financiers
CREATE TABLE IF NOT EXISTS s3_don_financier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    montant INT NOT NULL,
    date_don DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. Migrer les dons financiers existants depuis s3_don_details → s3_don_financier
--    (Les dons dont le besoin est de type 'argent' ou 'financier')
INSERT INTO s3_don_financier (montant, date_don)
SELECT dd.quantite, d.date_don
FROM s3_don d
JOIN s3_don_details dd ON d.id = dd.id_don
JOIN s3_besoin b ON dd.id_besoin = b.id
JOIN s3_type_besoin tb ON b.id_type_besoin = tb.id
WHERE tb.nom IN ('argent', 'financier');

-- 3. Supprimer les don_details qui référencent des besoins de type argent
DELETE dd FROM s3_don_details dd
JOIN s3_besoin b ON dd.id_besoin = b.id
JOIN s3_type_besoin tb ON b.id_type_besoin = tb.id
WHERE tb.nom IN ('argent', 'financier');

-- 4. Supprimer les s3_don orphelins (plus de détails associés)
DELETE d FROM s3_don d
LEFT JOIN s3_don_details dd ON d.id = dd.id_don
WHERE dd.id IS NULL;

-- 5. Supprimer les besoin_ville_details qui référencent des besoins argent (si existants)
DELETE bvd FROM s3_besoin_ville_details bvd
JOIN s3_besoin b ON bvd.id_besoin = b.id
JOIN s3_type_besoin tb ON b.id_type_besoin = tb.id
WHERE tb.nom IN ('argent', 'financier');

-- 6. Supprimer les achats qui référencent des besoins argent (si existants)
DELETE a FROM s3_achat a
JOIN s3_besoin b ON a.id_besoin = b.id
JOIN s3_type_besoin tb ON b.id_type_besoin = tb.id
WHERE tb.nom IN ('argent', 'financier');

-- 7. Supprimer les besoins de type argent (plus de FK qui les référencent)
DELETE b FROM s3_besoin b
JOIN s3_type_besoin tb ON b.id_type_besoin = tb.id
WHERE tb.nom IN ('argent', 'financier');

-- 8. Supprimer les types de besoin 'argent' et 'financier'
DELETE FROM s3_type_besoin WHERE nom IN ('argent', 'financier');

-- 9. Recréer la vue v_caisse pour lire depuis s3_don_financier
DROP VIEW IF EXISTS v_caisse;
CREATE VIEW v_caisse AS
SELECT id, date_don, montant
FROM s3_don_financier
ORDER BY date_don DESC;

-- 10. Recréer v_solde_caisse pour utiliser s3_don_financier
CREATE OR REPLACE VIEW v_solde_caisse AS
SELECT 
    COALESCE((SELECT SUM(montant) FROM s3_don_financier), 0) AS total_dons_argent,
    COALESCE((SELECT SUM(total) FROM s3_achat), 0) AS total_achats,
    COALESCE((SELECT SUM(montant) FROM s3_don_financier), 0) - COALESCE((SELECT SUM(total) FROM s3_achat), 0) AS solde;

-- 11. Recréer v_qte_dons_obtenus (maintenant uniquement nature/matériel)
CREATE OR REPLACE VIEW v_qte_dons_obtenus AS 
SELECT 
    b.id AS id_besoin,
    b.nom AS nom_besoin,
    SUM(dd.quantite) AS quantite
FROM s3_don d
JOIN s3_don_details dd ON d.id = dd.id_don
JOIN s3_besoin b ON b.id = dd.id_besoin
GROUP BY b.id, b.nom;
