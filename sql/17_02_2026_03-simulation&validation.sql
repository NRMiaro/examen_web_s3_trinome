CREATE OR REPLACE TABLE s3_dispatch_validation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Liens aux demandes originales
    id_besoin_ville INT NOT NULL,
    id_besoin INT NOT NULL,
    
    -- Résultats de l'allocation
    quantite_demandee INT NOT NULL,
    quantite_allouee INT NOT NULL,
    quantite_manquante INT NOT NULL,
    
    -- Statut de l'allocation
    statut ENUM('resolved', 'partial', 'unresolved') NOT NULL,
    
    -- Timestamps
    date_validation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign keys
    FOREIGN KEY (id_besoin_ville) REFERENCES s3_besoin_ville(id) ON DELETE CASCADE,
    FOREIGN KEY (id_besoin) REFERENCES s3_besoin(id) ON DELETE CASCADE
);

-- Vue pour récupérer les allocations validées par demande
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
JOIN s3_besoin_ville bv 
    ON dv.id_besoin_ville = bv.id
JOIN s3_ville v 
    ON bv.id_ville = v.id
JOIN s3_besoin b 
    ON dv.id_besoin = b.id
ORDER BY dv.date_validation DESC, b.nom;

-- Vue pour les statistiques globales du dispatch
CREATE OR REPLACE VIEW v_dispatch_stats AS
SELECT 
    COUNT(*) AS total_allocations,
    SUM(CASE WHEN statut = 'resolved' THEN 1 ELSE 0 END) AS allocations_completes,
    SUM(CASE WHEN statut = 'partial' THEN 1 ELSE 0 END) AS allocations_partielles,
    SUM(CASE WHEN statut = 'unresolved' THEN 1 ELSE 0 END) AS allocations_invalides,
    ROUND(SUM(CASE WHEN statut = 'resolved' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) AS pourcentage_resolu
FROM s3_dispatch_validation;

-- Vue pour les besoins totaux vs satisfaits en montant
CREATE OR REPLACE VIEW v_dispatch_montants AS
SELECT 
    b.id,
    b.nom AS besoin_nom,
    SUM(bvd.quantite) AS total_demande,
    SUM(COALESCE(dv.quantite_allouee, 0)) AS total_alloue,
    SUM(bvd.quantite) * b.prix AS montant_total_demande,
    SUM(COALESCE(dv.quantite_allouee, 0)) * b.prix AS montant_alloue,
    (SUM(bvd.quantite) - SUM(COALESCE(dv.quantite_allouee, 0))) * b.prix AS montant_restant
FROM s3_besoin b
LEFT JOIN s3_besoin_ville_details bvd 
    ON b.id = bvd.id_besoin
LEFT JOIN s3_dispatch_validation dv 
    ON bvd.id_besoin_ville = dv.id_besoin_ville AND b.id = dv.id_besoin
WHERE b.id_type_besoin IN (1, 2)  -- Exclure l'argent
GROUP BY b.id, b.nom, b.prix;
