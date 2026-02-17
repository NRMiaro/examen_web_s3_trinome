-- Le besoin "Argent" est désormais inséré via 17_02_2026_05_donnees_besoins_villes.sql

-- Recréer la vue caisse pour filtrer par type de besoin "financier"
DROP VIEW IF EXISTS v_caisse;

CREATE VIEW v_caisse AS
SELECT 
    d.id,
    d.date_don,
    dd.quantite as montant
FROM s3_don d
INNER JOIN s3_don_details dd ON d.id = dd.id_don
INNER JOIN s3_besoin b ON dd.id_besoin = b.id
INNER JOIN s3_type_besoin t ON b.id_type_besoin = t.id
WHERE t.nom = 'financier'
ORDER BY d.date_don DESC;
