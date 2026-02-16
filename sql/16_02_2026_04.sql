-- Créer le besoin "Argent" si ce n'existe pas et le lier au type de besoin approprié
-- On suppose que le type_besoin "financier" existe déjà
INSERT IGNORE INTO s3_besoin (nom, id_type_besoin, prix)
SELECT 'Argent', t.id, 0
FROM s3_type_besoin t
WHERE t.nom = 'financier'
LIMIT 1;

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
