-- Table des achats : on achète des besoins en nature/matériel avec l'argent des dons
CREATE TABLE IF NOT EXISTS s3_achat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_besoin INT NOT NULL,
    id_ville INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire INT NOT NULL,
    frais_pourcent DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    total INT NOT NULL,
    date_achat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_besoin) REFERENCES s3_besoin(id),
    FOREIGN KEY (id_ville) REFERENCES s3_ville(id)
);

-- Vue pour le solde de la caisse (dons argent - achats effectués)
CREATE OR REPLACE VIEW v_solde_caisse AS
SELECT 
    COALESCE((SELECT SUM(montant) FROM v_caisse), 0) AS total_dons_argent,
    COALESCE((SELECT SUM(total) FROM s3_achat), 0) AS total_achats,
    COALESCE((SELECT SUM(montant) FROM v_caisse), 0) - COALESCE((SELECT SUM(total) FROM s3_achat), 0) AS solde;
