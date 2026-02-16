-- Table pour les types de dons
CREATE TABLE s3_type_don (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    INDEX idx_nom (nom)
);

-- Insertion des types de dons basés sur les types de besoins
INSERT INTO s3_type_don (nom, description)
VALUES 
    ('nature', 'Dons en nature (denrées alimentaires, matériel)'),
    ('matériel', 'Dons de matériel et équipement'),
    ('financier', 'Dons d\'argent');

-- Vue pour la caisse - récupère les dons de type argent
CREATE VIEW v_caisse AS
SELECT 
    d.id,
    d.date_don,
    dd.quantite as montant
FROM s3_don d
INNER JOIN s3_don_details dd ON d.id = dd.id_don
INNER JOIN s3_besoin b ON dd.id_besoin = b.id
INNER JOIN s3_type_besoin t ON b.id_type_besoin = t.id
WHERE t.nom = 'argent'
ORDER BY d.date_don DESC;
